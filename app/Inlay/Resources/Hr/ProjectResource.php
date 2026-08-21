<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\Project;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\DatePicker;
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
use Inlay\Tables\Filters\SelectFilter;
use Inlay\Tables\Table;

final class ProjectResource extends Resource
{
    protected static string $model = Project::class;

    protected static ?string $label = 'Project';

    protected static ?string $pluralLabel = 'Projects';

    protected static ?string $navigationIcon = 'kanban-square';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 20;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'owner', 'status'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search projects…')->filters([
            SelectFilter::make('status')->options(['planned' => 'Planned', 'in-progress' => 'In progress', 'at-risk' => 'At risk', 'completed' => 'Completed']),
        ])->columns([
            TextColumn::make('name')->searchable()->sortable(), BadgeColumn::make('status')->colors(['planned' => 'gray', 'in-progress' => 'info', 'at-risk' => 'warning', 'completed' => 'success']), TextColumn::make('owner')->searchable()->sortable(), TextColumn::make('budget')->money('USD')->sortable()->alignment('right'), TextColumn::make('due_date')->date('M j, Y')->sortable(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/projects/{id}')->method('get'), Action::make('edit')->url('/admin/projects/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/projects/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save project')->schema([
            Section::make('project')->label('Project plan')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->autofocus(), Select::make('status')->options(['planned' => 'Planned', 'in-progress' => 'In progress', 'at-risk' => 'At risk', 'completed' => 'Completed'])->required(), TextInput::make('owner')->required(), TextInput::make('budget')->numeric()->prefix('$')->required(), DatePicker::make('due_date'),
                ]),
                Repeater::make('plan')->label('Milestones')->table([TableColumn::make('Type'), TableColumn::make('Title'), TableColumn::make('Owner')])->schema([
                    Select::make('type')->options(['milestone' => 'Milestone', 'checkpoint' => 'Checkpoint', 'task-group' => 'Task group'])->required(), TextInput::make('title')->required(), TextInput::make('owner'),
                ])->addActionLabel('Add milestone')->reorderable(),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Project'), TextEntry::make('status')->badge()->color('success'), TextEntry::make('owner'), TextEntry::make('budget')->money('USD'), TextEntry::make('due_date')->date('M j, Y'), TextEntry::make('plan')->label('Plan')->list()->bulleted()->columnSpanFull(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => ProjectList::route('/'), 'create' => ProjectCreate::route('/create'), 'view' => ProjectView::route('/{record}'), 'edit' => ProjectEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
