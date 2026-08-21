<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\Task;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Grid;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\BadgeColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class TaskResource extends Resource
{
    protected static string $model = Task::class;

    protected static ?string $label = 'Task';

    protected static ?string $pluralLabel = 'Tasks';

    protected static ?string $navigationIcon = 'check-square';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 50;

    public static function globallySearchableAttributes(): array
    {
        return ['title', 'project', 'assignee'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'title';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search tasks…')->columns([
            TextColumn::make('title')->searchable()->sortable()->limit(56), TextColumn::make('project')->searchable()->sortable(), TextColumn::make('assignee')->searchable(), BadgeColumn::make('priority')->colors(['low' => 'gray', 'normal' => 'info', 'high' => 'warning', 'urgent' => 'danger']), BadgeColumn::make('status')->colors(['todo' => 'gray', 'in-progress' => 'info', 'blocked' => 'danger', 'done' => 'success']), TextColumn::make('due_date')->date('M j, Y')->sortable(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/tasks/{id}')->method('get'), Action::make('edit')->url('/admin/tasks/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/tasks/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save task')->schema([Section::make('task')->label('Task details')->schema([Grid::make(2)->schema([
            TextInput::make('title')->required()->autofocus(), TextInput::make('project'), TextInput::make('assignee'), Select::make('priority')->options(['low' => 'Low', 'normal' => 'Normal', 'high' => 'High', 'urgent' => 'Urgent'])->required(), Select::make('status')->options(['todo' => 'To do', 'in-progress' => 'In progress', 'blocked' => 'Blocked', 'done' => 'Done'])->required(), DatePicker::make('due_date'), TextInput::make('estimate')->numeric()->suffix('hours'),
        ])])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([TextEntry::make('title')->label('Task')->columnSpanFull(), TextEntry::make('project'), TextEntry::make('assignee'), TextEntry::make('priority')->badge()->color('warning'), TextEntry::make('status')->badge()->color('success'), TextEntry::make('due_date')->date('M j, Y'), TextEntry::make('estimate')->suffix(' hours')]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => TaskList::route('/'), 'create' => TaskCreate::route('/create'), 'view' => TaskView::route('/{record}'), 'edit' => TaskEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
