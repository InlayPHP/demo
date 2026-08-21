<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Enums\Hr\ProjectStatus;
use App\Enums\Hr\TaskPriority;
use App\Inlay\RelationManagers\Hr\ProjectTimesheetsRelationManager;
use App\Inlay\RelationManagers\Hr\TasksRelationManager;
use App\Inlay\Widgets\Hr\ProjectStats;
use App\Models\Hr\Department;
use App\Models\Hr\Employee;
use App\Models\Hr\Project;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Actions\ActionModal;
use Inlay\Forms\Blocks\Block;
use Inlay\Forms\Fields\Builder;
use Inlay\Forms\Fields\ColorPicker;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\RichEditor;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\TagsInput;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\ToggleButtons;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\ColorEntry;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Tab;
use Inlay\Schemas\Components\Tabs;
use Inlay\Support\Condition;
use Inlay\Tables\Columns\Summarizers\Sum;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Filters\SelectFilter;
use Inlay\Tables\Filters\TrashedFilter;
use Inlay\Tables\Table;

final class ProjectResource extends Resource
{
    protected static string $model = Project::class;

    protected static ?string $label = 'Project';

    protected static ?string $pluralLabel = 'Projects';

    protected static ?string $navigationIcon = 'kanban-square';

    protected static ?string $navigationGroup = 'Projects';

    protected static int $navigationSort = 4;

    protected static bool $softDeletes = true;

    public static function recordTitleAttribute(): ?string
    {
        return 'name';
    }

    public static function widgets(): array
    {
        return [ProjectStats::class];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => ProjectStatus::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('priority')
                    ->badge()
                    ->color(fn (string $state): string => TaskPriority::tryFrom($state)?->color() ?? 'gray'),
                TextColumn::make('department.name')->sortable()->toggleable()->placeholder('No department'),
                TextColumn::make('budget')->money('USD')->sortable()->summarize(Sum::make()->money('USD')),
                TextColumn::make('spent')->money('USD')->sortable()->summarize(Sum::make()->money('USD')),
                TextColumn::make('progress')
                    ->state(fn (array $record): string => ((int) ($record['estimated_hours'] ?? 0)) > 0
                        ? number_format(((int) ($record['actual_hours'] ?? 0) / (int) $record['estimated_hours']) * 100, 0).'%'
                        : '0%'),
                TextColumn::make('start_date')->date()->sortable()->toggleable(),
                TextColumn::make('end_date')->date()->sortable()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(EnumOptions::map(ProjectStatus::class)),
                SelectFilter::make('priority')->options(EnumOptions::map(TaskPriority::class)),
                SelectFilter::make('department')->relationship('department', 'name'),
                TrashedFilter::make(),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('view')
                        ->label('View')
                        ->icon('eye')
                        ->url('/admin/projects/{id}')
                        ->method('get'),
                    Action::make('edit')
                        ->label('Edit')
                        ->icon('pencil')
                        ->url('/admin/projects/{id}/edit')
                        ->method('get'),
                    Action::make('change_status')
                        ->label('Change status')
                        ->icon('arrow-path-rounded-square')
                        ->color('primary')
                        ->modalWidth('md')
                        ->stickyModalFooter()
                        ->modalSubmitAction(Action::make('save')->label('Save')->color('primary'))
                        ->fillForm(fn (Project $record): array => ['status' => $record->status->value])
                        ->form([
                            ToggleButtons::make('status')->options(EnumOptions::map(ProjectStatus::class))->inline()->required(),
                        ])
                        ->authorizeUsing(HrActions::authorized())
                        ->action(fn (Project $record, array $data) => $record->update($data)),
                    Action::make('put_on_hold')
                        ->label('Put on hold')
                        ->icon('pause')
                        ->color('warning')
                        ->visibleWhen(Condition::make('status', ProjectStatus::Active->value))
                        ->modal(ActionModal::make('Put Project On Hold')
                            ->description('This will pause all work on this project.')
                            ->icon('exclamation-triangle', 'warning'))
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Project $record): void {
                            $record->update(['status' => ProjectStatus::OnHold]);
                            Notification::make()->title('Project put on hold')->warning()->send();
                        }),
                    Action::make('resume')
                        ->label('Resume')
                        ->icon('play')
                        ->color('success')
                        ->visibleWhen(Condition::make('status', ProjectStatus::OnHold->value))
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Project $record): void {
                            $record->update(['status' => ProjectStatus::Active]);
                            Notification::make()->title('Project resumed')->success()->send();
                        }),
                    Action::make('complete')
                        ->label('Complete')
                        ->icon('check-circle')
                        ->color('success')
                        ->visibleWhen(Condition::any(
                            Condition::make('status', ProjectStatus::Active->value),
                            Condition::make('status', ProjectStatus::OnHold->value),
                        ))
                        ->requiresConfirmation()
                        ->authorizeUsing(HrActions::authorized())
                        ->action(function (Project $record): void {
                            $record->update(['status' => ProjectStatus::Completed, 'end_date' => now()]);
                            Notification::make()->title('Project completed')->success()->send();
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

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save project')->schema([
            Tabs::make('Project')->columnSpanFull()->schema([
                Tab::make('Overview')->icon('information-circle')->columns(2)->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (string $operation, mixed $state, $set): void {
                            if ($operation === 'create') {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    TextInput::make('slug')
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->maxLength(255)
                        ->unique('hr_projects', 'slug', ignoreRecord: true),
                    RichEditor::make('description')->columnSpanFull(),
                    Select::make('department_id')
                        ->label('Department')
                        ->options(fn (): array => Department::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable(),
                    ToggleButtons::make('status')
                        ->options(EnumOptions::map(ProjectStatus::class))
                        ->inline()
                        ->required()
                        ->default(ProjectStatus::Planning->value),
                    ToggleButtons::make('priority')
                        ->options(EnumOptions::map(TaskPriority::class))
                        ->inline()
                        ->required()
                        ->default(TaskPriority::Medium->value),
                    ColorPicker::make('color'),
                    DatePicker::make('start_date')->required(),
                    DatePicker::make('end_date')->after('start_date', orEqual: true),
                ]),
                Tab::make('Plan')->icon('clipboard-document-list')->schema([
                    Builder::make('plan')->hiddenLabel()->blocks([
                        Block::make('milestone')
                            ->icon('flag')
                            ->schema([
                                TextInput::make('title')->required(),
                                DatePicker::make('target_date')->required(),
                                Textarea::make('description')->rows(2),
                            ]),
                        Block::make('task_group')
                            ->label('Task group')
                            ->icon('list-bullet')
                            ->schema([
                                TextInput::make('title')->required(),
                                Select::make('assignee')
                                    ->options(fn (): array => Employee::query()->orderBy('name')->pluck('name', 'id')->all()),
                                TagsInput::make('tasks')->placeholder('Add tasks'),
                            ]),
                        Block::make('checkpoint')
                            ->icon('check-circle')
                            ->schema([
                                TextInput::make('title')->required(),
                                DatePicker::make('date')->required(),
                                Select::make('status')
                                    ->options([
                                        'pending' => 'Pending',
                                        'passed' => 'Passed',
                                        'failed' => 'Failed',
                                    ]),
                            ]),
                    ])->columnSpanFull(),
                ]),
                Tab::make('Budget')->icon('currency-dollar')->columns(2)->schema([
                    TextInput::make('budget')
                        ->required()
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->maxValue(9999999999.99)
                        ->default(0),
                    TextInput::make('spent')
                        ->numeric()
                        ->prefix('$')
                        ->minValue(0)
                        ->maxValue(9999999999.99)
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->default(0),
                    TextInput::make('estimated_hours')
                        ->numeric()
                        ->suffix('hours')
                        ->minValue(0)
                        ->maxValue(9999999.9)
                        ->required()
                        ->default(0),
                    TextInput::make('actual_hours')
                        ->numeric()
                        ->suffix('hours')
                        ->minValue(0)
                        ->maxValue(9999999.9)
                        ->disabled()
                        ->dehydrated()
                        ->required()
                        ->default(0),
                ]),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Tabs::make('Project')->columnSpanFull()->schema([
                Tab::make('Overview')->icon('information-circle')->columns(2)->schema([
                    TextEntry::make('name'),
                    TextEntry::make('slug'),
                    TextEntry::make('department.name')->placeholder('No department'),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (Project $record): string => $record->status->color()),
                    TextEntry::make('priority')
                        ->badge()
                        ->color(fn (Project $record): string => $record->priority->color()),
                    ColorEntry::make('color')->placeholder('No color'),
                    TextEntry::make('start_date')->date(),
                    TextEntry::make('end_date')->date()->placeholder('No end date'),
                    TextEntry::make('description')->prose()->markdown()->columnSpanFull()->placeholder('No description'),
                ]),
                Tab::make('Budget')->icon('currency-dollar')->columns(2)->schema([
                    TextEntry::make('budget')->money('USD')->placeholder('$0.00'),
                    TextEntry::make('spent')->money('USD')->placeholder('$0.00'),
                    TextEntry::make('estimated_hours')->suffix(' hours')->placeholder('0'),
                    TextEntry::make('actual_hours')->suffix(' hours')->placeholder('0'),
                ]),
            ]),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getRelations(): array
    {
        return [TasksRelationManager::class, ProjectTimesheetsRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ProjectList::route('/'),
            'create' => ProjectCreate::route('/create'),
            'edit' => ProjectEdit::route('/{record}/edit'),
            'view' => ProjectView::route('/{record}'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
