<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Enums\Hr\ExpenseCategory;
use App\Enums\Hr\ExpenseStatus;
use App\Inlay\Widgets\Hr\ExpenseStats;
use App\Models\Hr\Employee;
use App\Models\Hr\Expense;
use App\Models\Hr\Project;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\FileUpload;
use Inlay\Forms\Fields\Placeholder;
use Inlay\Forms\Fields\Repeater;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\ToggleButtons;
use Inlay\Forms\Form;
use Inlay\Forms\Repeater\TableColumn;
use Inlay\Infolists\Entries\RepeatableEntry;
use Inlay\Infolists\Entries\RepeatableEntry\TableColumn as InfolistTableColumn;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Section;
use Inlay\Schemas\Components\Wizard;
use Inlay\Schemas\Components\WizardStep;
use Inlay\Support\Condition;
use Inlay\Tables\Columns\Summarizers\Sum;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class ExpenseResource extends Resource
{
    protected static string $model = Expense::class;

    protected static ?string $label = 'Expense';

    protected static ?string $pluralLabel = 'Expenses';

    protected static ?string $navigationIcon = 'receipt';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 3;

    public static function recordTitleAttribute(): ?string
    {
        return 'expense_number';
    }

    public static function widgets(): array
    {
        return [ExpenseStats::class];
    }

    /** Shared line-items repeater used by the create wizard and the edit form. */
    private static function lineItemsRepeater(): Repeater
    {
        return Repeater::make('expenseLines')
            ->relationship()
            ->minItems(1)
            ->table([
                TableColumn::make('Description'),
                TableColumn::make('Quantity')->width('100px'),
                TableColumn::make('Unit Price')->width('120px'),
                TableColumn::make('Amount')->width('120px'),
                TableColumn::make('Date')->width('150px'),
            ])
            ->schema([
                TextInput::make('description')->required()->maxLength(255),
                TextInput::make('quantity')
                    ->integer()
                    ->minValue(1)
                    ->maxValue(2147483647)
                    ->default(1)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => self::recomputeLineAmount($get, $set)),
                TextInput::make('unit_price')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => self::recomputeLineAmount($get, $set)),
                TextInput::make('amount')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->disabled()
                    ->dehydrated(),
                DatePicker::make('date')->required()->default(now()),
            ])
            ->columnSpanFull();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('expense_number')->searchable()->sortable()->weight('medium'),
                TextColumn::make('employee.name')->searchable()->sortable(),
                TextColumn::make('category')
                    ->badge()
                    ->color(fn (string $state): string => ExpenseCategory::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => ExpenseStatus::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('total_amount')->money('USD')->sortable()->summarize(Sum::make()->money('USD')),
                TextColumn::make('project.name')->sortable()->toggleable()->placeholder('No project'),
                TextColumn::make('submitted_at')->dateTime()->sortable()->toggleable(),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('view')
                        ->label('View')
                        ->icon('eye')
                        ->url('/admin/expenses/{id}')
                        ->method('get'),
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->url('/admin/expenses/{id}/edit')
                        ->method('get'),
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('check')
                        ->color('success')
                        ->visibleWhen(Condition::make('status', ExpenseStatus::Submitted->value))
                        ->requiresConfirmation()
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Expense $record): void {
                            $record->update(['status' => ExpenseStatus::Approved, 'approved_at' => now()]);
                            Notification::make()->title('Expense approved')->success()->send();
                        }),
                    Action::make('reject')
                        ->label('Reject')
                        ->icon('x-mark')
                        ->color('danger')
                        ->visibleWhen(Condition::make('status', ExpenseStatus::Submitted->value))
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('reject')->label('Reject')->color('danger'))
                        ->form([
                            Textarea::make('rejection_reason')->label('Reason for rejection')->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Expense $record, array $data): void {
                            $record->update([
                                'status' => ExpenseStatus::Rejected,
                                'notes' => $data['rejection_reason'],
                            ]);
                            Notification::make()->title('Expense rejected')->danger()->send();
                        }),
                    Action::make('submit')
                        ->label('Submit')
                        ->icon('paper-airplane')
                        ->color('info')
                        ->visibleWhen(Condition::make('status', ExpenseStatus::Draft->value))
                        ->requiresConfirmation()
                        ->before(function (Expense $record): void {
                            if ($record->total_amount <= 0) {
                                Notification::make()
                                    ->title('Cannot submit an expense with no amount')
                                    ->danger()
                                    ->send();
                                throw ValidationException::withMessages([]);
                            }
                        })
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Expense $record): void {
                            $record->update(['status' => ExpenseStatus::Submitted, 'submitted_at' => now()]);
                        })
                        ->after(function (Expense $record): void {
                            Notification::make()
                                ->title("Expense {$record->expense_number} submitted for approval")
                                ->success()
                                ->send();
                        }),
                    Action::make('reimburse')
                        ->label('Reimburse')
                        ->icon('banknotes')
                        ->color('primary')
                        ->visibleWhen(Condition::make('status', ExpenseStatus::Approved->value))
                        ->requiresConfirmation()
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Expense $record): void {
                            $record->update(['status' => ExpenseStatus::Reimbursed]);
                            Notification::make()->title('Expense reimbursed')->success()->send();
                        }),
                    Action::make('flag')
                        ->label('Flag')
                        ->icon('flag')
                        ->color('warning')
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('flag')->label('Flag')->color('warning'))
                        ->form([
                            Textarea::make('flag_reason')->label('Reason for flagging')->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Expense $record, array $data): void {
                            $record->update([
                                'status' => ExpenseStatus::Draft,
                                'notes' => $data['flag_reason'],
                            ]);
                            Notification::make()->title('Expense flagged and returned to draft')->warning()->send();
                        }),
                    HrActions::cheekyDelete(),                ])
                    ->icon('ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Row actions')
                    ->dropdownPlacement('left-start'),
            ])
            ->bulkActions([
                HrActions::cheekyDeleteBulk(),
            ]);
    }

    /** The section-based edit form (the create page uses a wizard instead). */
    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save expense')->schema([
            Section::make('details')->label('Details')->columns(2)->columnSpanFull()->schema([
                TextInput::make('expense_number')
                    ->default(fn (): string => 'EXP-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT))
                    ->disabled()
                    ->dehydrated()
                    ->required()
                    ->maxLength(255),
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                Select::make('project_id')
                    ->label('Project')
                    ->options(fn (): array => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                ToggleButtons::make('category')
                    ->options(EnumOptions::options(ExpenseCategory::class))
                    ->inline()
                    ->required()
                    ->columnSpanFull(),
                ToggleButtons::make('status')
                    ->options(EnumOptions::options(ExpenseStatus::class))
                    ->inline()
                    ->required()
                    ->default(ExpenseStatus::Draft->value)
                    ->columnSpanFull(),
                Textarea::make('description')->required()->maxLength(65535)->columnSpanFull(),
            ]),
            Section::make('line_items')->label('Line Items')->columnSpanFull()->schema([
                self::lineItemsRepeater()->hiddenLabel(),
            ]),
            Section::make('summary')->label('Summary')->columns(2)->columnSpanFull()->schema([
                TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('$')
                    ->minValue(0)
                    ->maxValue(99999999.99)
                    ->disabled()
                    ->dehydrated(),
                Select::make('currency')
                    ->required()
                    ->options(['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'CAD' => 'CAD'])
                    ->default('USD'),
                FileUpload::make('receipt_path')->directory('expense-receipts'),
                Textarea::make('notes')->maxLength(65535)->columnSpanFull(),
            ]),
        ]);
    }

    /** The three-step create wizard used by the CreateExpense page. */
    public static function createWizardForm(): Form
    {
        return Form::make('expenses.create.wizard')
            // The relationship lines repeater needs a model context even
            // before the record exists; a class-string satisfies it (the
            // record is created at the end of the wizard, then lines save
            // through the relationship).
            ->model(Expense::class)
            ->schema([
                Wizard::make('expense')->validateSteps()->steps([
                    WizardStep::make('Details')->icon('information-circle')->columns(2)->schema([
                        TextInput::make('expense_number')
                            ->default(fn (): string => 'EXP-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT))
                            ->disabled()
                            ->dehydrated()
                            ->required()
                            ->maxLength(255),
                        Select::make('employee_id')
                            ->label('Employee')
                            ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),
                        Select::make('project_id')
                            ->label('Project')
                            ->options(fn (): array => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable(),
                        ToggleButtons::make('category')
                            ->options(EnumOptions::options(ExpenseCategory::class))
                            ->inline()
                            ->required()
                            ->columnSpanFull(),
                        Textarea::make('description')->required()->maxLength(65535)->columnSpanFull(),
                        Placeholder::make('policy_notice')
                            ->content('Expenses over $500 require manager approval. Expenses over $5,000 require VP approval.')
                            ->columnSpanFull(),
                    ]),
                    WizardStep::make('Line Items')->icon('list-bullet')->schema([
                        self::lineItemsRepeater(),
                    ]),
                    WizardStep::make('Review')->icon('check-circle')->columns(2)->schema([
                        TextInput::make('total_amount')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->maxValue(99999999.99)
                            ->disabled()
                            ->dehydrated(),
                        Select::make('currency')
                            ->required()
                            ->options(['USD' => 'USD', 'EUR' => 'EUR', 'GBP' => 'GBP', 'CAD' => 'CAD'])
                            ->default('USD'),
                        FileUpload::make('receipt_path')->directory('expense-receipts'),
                        Textarea::make('notes')->maxLength(65535)->columnSpanFull(),
                    ]),
                ])->columnSpanFull(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('expense_details')->label('Expense Details')->columns(3)->columnSpanFull()->schema([
                TextEntry::make('expense_number'),
                TextEntry::make('employee.name'),
                TextEntry::make('category')
                    ->badge()
                    ->color(fn (Expense $record): string => $record->category->color()),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (Expense $record): string => $record->status->color()),
                TextEntry::make('total_amount')->money('USD'),
                TextEntry::make('currency'),
                TextEntry::make('project.name')->placeholder('No project'),
                TextEntry::make('submitted_at')->dateTime()->placeholder('Not submitted'),
                TextEntry::make('approved_at')->dateTime()->placeholder('Not approved'),
                TextEntry::make('description')->columnSpanFull(),
                TextEntry::make('notes')->placeholder('No notes')->columnSpanFull(),
            ]),
            Section::make('line_items')->label('Line Items')->columnSpanFull()->schema([
                RepeatableEntry::make('expenseLines')
                    ->hiddenLabel()
                    ->table([
                        InfolistTableColumn::make('Description'),
                        InfolistTableColumn::make('Quantity')->width('100px'),
                        InfolistTableColumn::make('Unit Price')->width('120px'),
                        InfolistTableColumn::make('Amount')->width('120px'),
                        InfolistTableColumn::make('Date')->width('150px'),
                    ])
                    ->schema([
                        TextEntry::make('description'),
                        TextEntry::make('quantity'),
                        TextEntry::make('unit_price')->money('USD'),
                        TextEntry::make('amount')->money('USD'),
                        TextEntry::make('date')->date(),
                    ]),
            ]),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    /**
     * The create wizard has no status field; every new expense starts as a
     * draft, and line amounts are recomputed authoritatively from the payload.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function mutateDataBeforeCreate(array $data): array
    {
        $data['status'] = ExpenseStatus::Draft->value;
        $data['expense_number'] ??= 'EXP-'.str_pad((string) random_int(1, 999999), 6, '0', STR_PAD_LEFT);
        $data = self::recomputeLines($data);

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function mutateDataBeforeUpdate(array $data, Model $record): array
    {
        return self::recomputeLines($data);
    }

    /**
     * Recompute every line amount (quantity x unit price) and the expense
     * total (sum of line amounts), matching the create/edit page hooks in the
     * Filament source.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function recomputeLines(array $data): array
    {
        $lines = $data['expenseLines'] ?? [];

        foreach ($lines as &$line) {
            $line['amount'] = (int) ($line['quantity'] ?? 1) * (float) ($line['unit_price'] ?? 0);
        }
        unset($line);

        $data['expenseLines'] = $lines;
        $data['total_amount'] = collect($lines)->sum(fn (array $line): float => (float) ($line['amount'] ?? 0));

        return $data;
    }

    private static function recomputeLineAmount($get, $set): void
    {
        $quantity = (int) ($get('quantity') ?? 1);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $set('amount', number_format($quantity * $unitPrice, 2, '.', ''));
    }

    public static function getPages(): array
    {
        return [
            'index' => ExpenseList::route('/'),
            'create' => ExpenseCreate::route('/create'),
            'edit' => ExpenseEdit::route('/{record}/edit'),
            'view' => ExpenseView::route('/{record}'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
