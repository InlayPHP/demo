<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Enums\Hr\LeaveStatus;
use App\Enums\Hr\LeaveType;
use App\Models\Hr\Employee;
use App\Models\Hr\LeaveRequest;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Actions\BulkAction;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\Placeholder;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\TimePicker;
use Inlay\Forms\Fields\ToggleButtons;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Section;
use Inlay\Support\Condition;
use Inlay\Tables\Columns\SelectColumn;
use Inlay\Tables\Columns\Summarizers\Sum;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class LeaveRequestResource extends Resource
{
    protected static string $model = LeaveRequest::class;

    protected static ?string $label = 'Leave Request';

    protected static ?string $pluralLabel = 'Leave Requests';

    protected static ?string $navigationIcon = 'calendar-days';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 2;

    // NOTE: the Inlay framework reads navigation badges from the static
    // $navigationBadge property, so a live "pending count" badge cannot be
    // computed per request (framework limitation; documented in the report).

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('start_date', 'desc')
            ->columns([
                TextColumn::make('employee.name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => LeaveType::tryFrom($state)?->color() ?? 'gray'),
                SelectColumn::make('status')
                    ->options(EnumOptions::map(LeaveStatus::class))
                    ->rules(['required']),
                TextColumn::make('start_date')->date()->sortable(),
                TextColumn::make('end_date')->date()->sortable(),
                TextColumn::make('days_requested')->numeric(1)->sortable()->summarize(Sum::make()),
                TextColumn::make('approver.name')->toggleable(isToggledHiddenByDefault: true)->placeholder('Not assigned'),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('approve')
                        ->label('Approve')
                        ->icon('check')
                        ->color('success')
                        ->visibleWhen(Condition::make('status', LeaveStatus::Pending->value))
                        ->requiresConfirmation()
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (LeaveRequest $record): void {
                            $record->update(['status' => LeaveStatus::Approved, 'reviewed_at' => now()]);
                            Notification::make()->title('Leave request approved')->success()->send();
                        }),
                    Action::make('reject')
                        ->label('Reject')
                        ->icon('x-mark')
                        ->color('danger')
                        ->visibleWhen(Condition::make('status', LeaveStatus::Pending->value))
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('reject')->label('Reject')->color('danger'))
                        ->form([
                            Textarea::make('reviewer_notes')->label('Reason for rejection')->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (LeaveRequest $record, array $data): void {
                            $record->update([
                                'status' => LeaveStatus::Rejected,
                                'reviewer_notes' => $data['reviewer_notes'],
                                'reviewed_at' => now(),
                            ]);
                            Notification::make()->title('Leave request rejected')->danger()->send();
                        }),
                    Action::make('view')
                        ->label('View')
                        ->icon('eye')
                        ->url('/admin/leave-requests/{id}')
                        ->method('get'),
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->url('/admin/leave-requests/{id}/edit')
                        ->method('get'),
                    HrActions::cheekyDelete(),                ])
                    ->icon('ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Row actions')
                    ->dropdownPlacement('left-start'),
            ])
            ->bulkActions([
                BulkAction::make('approve')
                    ->label('Approve selected')
                    ->icon('check')
                    ->color('success')
                    ->requiresConfirmation()
                    ->authorizeUsing(HrActions::authorized())
                    ->action(function (Collection $records): void {
                        $records->each(function (LeaveRequest $record): void {
                            if ($record->status === LeaveStatus::Pending) {
                                $record->update(['status' => LeaveStatus::Approved, 'reviewed_at' => now()]);
                            }
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('reject')
                    ->label('Reject selected')
                    ->icon('x-mark')
                    ->color('danger')
                    ->form([
                        Textarea::make('reviewer_notes')->label('Reason for rejection')->required(),
                    ])
                    ->authorizeUsing(HrActions::authorized())
                    ->action(function (Collection $records, array $data): void {
                        $records->each(function (LeaveRequest $record) use ($data): void {
                            if ($record->status === LeaveStatus::Pending) {
                                $record->update([
                                    'status' => LeaveStatus::Rejected,
                                    'reviewer_notes' => $data['reviewer_notes'],
                                    'reviewed_at' => now(),
                                ]);
                            }
                        });
                    })
                    ->deselectRecordsAfterCompletion(),
                HrActions::cheekyDeleteBulk(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save leave request')->schema([
            Section::make('leave_details')->label('Leave Details')->columns(2)->columnSpanFull()->schema([
                Select::make('employee_id')
                    ->label('Employee')
                    ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                ToggleButtons::make('type')
                    ->options(EnumOptions::options(LeaveType::class))
                    ->inline()
                    ->required()
                    ->columnSpanFull(),
                DatePicker::make('start_date')
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn ($get, $set) => self::calculateDays($get, $set)),
                DatePicker::make('end_date')
                    ->required()
                    ->after('start_date', orEqual: true)
                    ->live()
                    ->afterStateUpdated(fn ($get, $set) => self::calculateDays($get, $set)),
                TimePicker::make('start_time')->seconds(false)->label('Start time (half days)'),
                TimePicker::make('end_time')->seconds(false)->label('End time (half days)'),
                TextInput::make('days_requested')
                    ->numeric()
                    ->step(0.5)
                    ->minValue(0)
                    ->maxValue(999.9)
                    ->disabled()
                    ->dehydrated()
                    ->required(),
                Textarea::make('reason')->required()->maxLength(65535)->columnSpanFull(),
            ]),
            Section::make('review')->label('Review')->visible(fn ($operation) => $operation !== 'create')->columns(2)->columnSpanFull()->schema([
                Placeholder::make('notice')
                    ->label('Notice')
                    ->content('Note: Approving this request will deduct days from the employee\'s leave balance.')
                    ->columnSpanFull(),
                ToggleButtons::make('status')
                    ->options(EnumOptions::map(LeaveStatus::class))
                    ->inline()
                    ->required()
                    ->default(LeaveStatus::Pending->value)
                    ->columnSpanFull(),
                Select::make('approver_id')
                    ->label('Approver')
                    ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                Textarea::make('reviewer_notes')->maxLength(65535),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('leave_details')->label('Leave Details')->columns(3)->columnSpanFull()->schema([
                TextEntry::make('employee.name'),
                TextEntry::make('type')
                    ->badge()
                    ->color(fn (LeaveRequest $record): string => $record->type->color()),
                TextEntry::make('status')
                    ->badge()
                    ->color(fn (LeaveRequest $record): string => $record->status->color()),
                TextEntry::make('start_date')->date(),
                TextEntry::make('end_date')->date(),
                TextEntry::make('days_requested')->suffix(' days'),
                TextEntry::make('start_time')->label('Start time (half days)')->time('H:i')->placeholder('N/A'),
                TextEntry::make('end_time')->label('End time (half days)')->time('H:i')->placeholder('N/A'),
                TextEntry::make('reason')->columnSpanFull(),
            ]),
            Section::make('review')->label('Review')->columns(2)->columnSpanFull()->schema([
                TextEntry::make('approver.name')->placeholder('Not yet assigned'),
                TextEntry::make('reviewed_at')->label('Reviewed at')->dateTime()->placeholder('Not yet reviewed'),
                TextEntry::make('reviewer_notes')->placeholder('No notes')->columnSpanFull(),
            ]),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    /** @param array<string, mixed> $data */
    protected static function mutateDataBeforeCreate(array $data): array
    {
        return self::computeDays($data);
    }

    /** @param array<string, mixed> $data */
    protected static function mutateDataBeforeUpdate(array $data, Model $record): array
    {
        return self::computeDays($data);
    }

    /** @param array<string, mixed> $data */
    private static function computeDays(array $data): array
    {
        if (! empty($data['start_date']) && ! empty($data['end_date'])) {
            $days = Carbon::parse($data['start_date'])->diffInDays(Carbon::parse($data['end_date'])) + 1;
            $data['days_requested'] = max(0.5, $days);
        }

        return $data;
    }

    public static function getPages(): array
    {
        return [
            'index' => LeaveRequestList::route('/'),
            'create' => LeaveRequestCreate::route('/create'),
            'edit' => LeaveRequestEdit::route('/{record}/edit'),
            'view' => LeaveRequestView::route('/{record}'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
