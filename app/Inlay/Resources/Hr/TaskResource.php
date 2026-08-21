<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Enums\Hr\TaskPriority;
use App\Enums\Hr\TaskStatus;
use App\Models\Hr\Employee;
use App\Models\Hr\Project;
use App\Models\Hr\Task;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Actions\BulkAction;
use Inlay\Forms\Fields\CheckboxList;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\DateTimePicker;
use Inlay\Forms\Fields\Radio;
use Inlay\Forms\Fields\RichEditor;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\ToggleButtons;
use Inlay\Forms\Form;
use Inlay\Infolists\Infolist;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Section;
use Inlay\Support\Condition;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Filters\SelectFilter;
use Inlay\Tables\Table;

final class TaskResource extends Resource
{
    protected static string $model = Task::class;

    protected static ?string $label = 'Task';

    protected static ?string $pluralLabel = 'Tasks';

    protected static ?string $navigationIcon = 'check-square';

    protected static ?string $navigationGroup = 'Projects';

    protected static int $navigationSort = 5;

    public static function recordTitleAttribute(): ?string
    {
        return 'title';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable()->weight('medium')->limit(40),
                TextColumn::make('project.name')->searchable()->sortable(),
                TextColumn::make('assignee.name')->searchable()->sortable()->placeholder('Unassigned'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => TaskStatus::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => TaskPriority::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('estimated_hours')->numeric(1)->sortable()->toggleable(),
                TextColumn::make('due_date')->date()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(EnumOptions::map(TaskStatus::class)),
                SelectFilter::make('priority')->options(EnumOptions::map(TaskPriority::class)),
                SelectFilter::make('project')->relationship('project', 'name'),
                SelectFilter::make('assignee')->relationship('assignee', 'name'),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->url('/admin/tasks/{id}/edit')
                        ->method('get'),
                    Action::make('start')
                        ->label('Start')
                        ->icon('play')
                        ->color('success')
                        ->visibleWhen(Condition::any(
                            Condition::make('status', TaskStatus::Backlog->value),
                            Condition::make('status', TaskStatus::Todo->value),
                        ))
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Task $record): void {
                            $record->update(['status' => TaskStatus::InProgress]);
                            Notification::make()->title('Task started')->success()->send();
                        }),
                    Action::make('send_to_review')
                        ->label('Send to review')
                        ->icon('eye')
                        ->color('primary')
                        ->visibleWhen(Condition::make('status', TaskStatus::InProgress->value))
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Task $record): void {
                            $record->update(['status' => TaskStatus::InReview]);
                            Notification::make()->title('Task sent to review')->success()->send();
                        }),
                    Action::make('complete')
                        ->label('Complete')
                        ->icon('check-circle')
                        ->color('success')
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('complete')->label('Complete')->color('success'))
                        ->visibleWhen(Condition::any(
                            Condition::make('status', TaskStatus::InProgress->value),
                            Condition::make('status', TaskStatus::InReview->value),
                        ))
                        ->fillForm(fn (Task $record): array => ['actual_hours' => $record->actual_hours])
                        ->form([
                            TextInput::make('actual_hours')
                                ->numeric()
                                ->step(0.5)
                                ->minValue(0)
                                ->maxValue(99999.9)
                                ->suffix('hours'),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Task $record, array $data): void {
                            $record->update([
                                'status' => TaskStatus::Completed,
                                'completed_at' => now(),
                                'actual_hours' => $data['actual_hours'],
                            ]);
                            Notification::make()->title('Task completed')->success()->send();
                        }),
                    Action::make('assign')
                        ->label('Assign')
                        ->icon('user-plus')
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('assign')->label('Assign')->color('primary'))
                        ->form([
                            Select::make('assigned_to')
                                ->label('Assignee')
                                ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                                ->searchable()
                                ->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Task $record, array $data) => $record->update($data)),
                    Action::make('set_priority')
                        ->label('Set priority')
                        ->icon('flag')
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('save')->label('Save')->color('primary'))
                        ->form([
                            ToggleButtons::make('priority')->options(EnumOptions::map(TaskPriority::class))->inline()->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Task $record, array $data) => $record->update($data)),
                    Action::make('replicate')
                        ->label('Replicate')
                        ->icon('copy')
                        ->requiresConfirmation()
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Task $record): void {
                            $replica = $record->replicate(['id', 'completed_at', 'actual_hours', 'sort']);
                            $replica->save();
                        }),
                    HrActions::cheekyDelete(),
                ])
                    ->icon('ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Row actions')
                    ->dropdownPlacement('left-start'),
            ])
            ->bulkActions([
                BulkAction::make('set_status')
                    ->label('Set status')
                    ->icon('arrow-path-rounded-square')
                    ->color('primary')
                    ->form([
                        ToggleButtons::make('status')->options(EnumOptions::map(TaskStatus::class))->inline()->required(),
                    ])
                    ->authorizeUsing(HrActions::authorized())
                    ->action(function (Collection $records, array $data): void {
                        $records->each(fn (Task $record) => $record->update($data));
                        Notification::make()
                            ->title('Updated '.$records->count().' tasks to '.(TaskStatus::tryFrom((string) $data['status'])?->label() ?? (string) $data['status']))
                            ->success()
                            ->send();
                    })
                    ->deselectRecordsAfterCompletion(),
                BulkAction::make('assign')
                    ->label('Assign')
                    ->icon('user-plus')
                    ->color('info')
                    ->form([
                        Select::make('assigned_to')
                            ->label('Assignee')
                            ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->required(),
                    ])
                    ->authorizeUsing(HrActions::authorized())
                    ->action(fn (Collection $records, array $data) => $records->each(
                        fn (Task $record) => $record->update($data),
                    ))
                    ->deselectRecordsAfterCompletion(),
                HrActions::cheekyDeleteBulk(),
            ])
            ->recordClasses(fn (Task $record): ?string => match (true) {
                $record->status === TaskStatus::Completed => 'opacity-60',
                $record->due_date !== null && $record->due_date->isPast() => 'bg-danger-50 dark:bg-danger-950/50',
                default => null,
            });
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save task')->schema([
            Section::make('task_details')->label('Task Details')->columns(2)->columnSpanFull()->schema([
                TextInput::make('title')->required()->maxLength(255),
                Select::make('project_id')
                    ->label('Project')
                    ->options(fn (): array => Project::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->required(),
                Select::make('assigned_to')
                    ->label('Assignee')
                    ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable(),
                ToggleButtons::make('status')
                    ->options(EnumOptions::map(TaskStatus::class))
                    ->inline()
                    ->required()
                    ->live()
                    ->default(TaskStatus::Backlog->value)
                    ->columnSpanFull(),
                Radio::make('priority')
                    ->options(EnumOptions::map(TaskPriority::class))
                    ->inline()
                    ->required()
                    ->default(TaskPriority::Medium->value),
                TextInput::make('estimated_hours')
                    ->numeric()
                    ->step(0.5)
                    ->minValue(0)
                    ->maxValue(99999.9)
                    ->suffix('hours'),
                DatePicker::make('due_date'),
                RichEditor::make('description')->columnSpanFull(),
                CheckboxList::make('labels')
                    ->options([
                        'bug' => 'Bug',
                        'feature' => 'Feature',
                        'enhancement' => 'Enhancement',
                        'documentation' => 'Documentation',
                        'design' => 'Design',
                        'testing' => 'Testing',
                        'refactor' => 'Refactor',
                        'urgent' => 'Urgent',
                    ])
                    ->columnSpanFull(),
                DateTimePicker::make('completed_at')
                    ->visible(fn ($get): bool => in_array($get('status'), [TaskStatus::Completed->value], true)),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist;
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => TaskList::route('/'),
            'create' => TaskCreate::route('/create'),
            'edit' => TaskEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
