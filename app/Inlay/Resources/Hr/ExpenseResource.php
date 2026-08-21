<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\Expense;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\DateTimePicker;
use Inlay\Forms\Fields\Repeater;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Forms\Repeater\TableColumn;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Grid;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\BadgeColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class ExpenseResource extends Resource
{
    protected static string $model = Expense::class;

    protected static ?string $label = 'Expense';

    protected static ?string $pluralLabel = 'Expenses';

    protected static ?string $navigationIcon = 'receipt';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 40;

    public static function globallySearchableAttributes(): array
    {
        return ['employee', 'category', 'status'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'employee';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search expenses…')->columns([
            TextColumn::make('employee')->searchable()->sortable(), TextColumn::make('category')->sortable(), BadgeColumn::make('status')->colors(['submitted' => 'warning', 'approved' => 'info', 'rejected' => 'danger', 'reimbursed' => 'success']), TextColumn::make('amount')->money('USD')->sortable()->alignment('right'), TextColumn::make('submitted_at')->since()->sortable(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/expenses/{id}')->method('get'), Action::make('edit')->url('/admin/expenses/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/expenses/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save expense')->schema([
            Section::make('expense')->label('Expense claim')->schema([
                Grid::make(2)->schema([
                    TextInput::make('employee')->required()->autofocus(), Select::make('category')->options(['Travel' => 'Travel', 'Equipment' => 'Equipment', 'Meals' => 'Meals', 'Training' => 'Training', 'Software' => 'Software'])->required(), Select::make('status')->options(['submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'reimbursed' => 'Reimbursed'])->required(), TextInput::make('amount')->numeric()->prefix('$')->required(), DateTimePicker::make('submitted_at')->required(), DateTimePicker::make('approved_at'),
                ]),
                Repeater::make('line_items')->label('Expense line items')->table([TableColumn::make('Description'), TableColumn::make('Amount')])->schema([TextInput::make('description')->required(), TextInput::make('amount')->numeric()->prefix('$')->required()])->addActionLabel('Add line item'),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('employee')->label('Employee'), TextEntry::make('category'), TextEntry::make('status')->badge()->color('success'), TextEntry::make('amount')->money('USD'), TextEntry::make('submitted_at')->dateTime('M j, Y H:i'), TextEntry::make('approved_at')->dateTime('M j, Y H:i')->placeholder('Not approved'), TextEntry::make('line_items')->label('Line items')->list()->bulleted()->columnSpanFull(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => ExpenseList::route('/'), 'create' => ExpenseCreate::route('/create'), 'view' => ExpenseView::route('/{record}'), 'edit' => ExpenseEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
