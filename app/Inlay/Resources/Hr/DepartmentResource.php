<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Models\Hr\Department;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
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

final class DepartmentResource extends Resource
{
    protected static string $model = Department::class;

    protected static ?string $label = 'Department';

    protected static ?string $pluralLabel = 'Departments';

    protected static ?string $navigationIcon = 'building-2';

    protected static ?string $navigationGroup = 'HR';

    protected static int $navigationSort = 5;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'parent', 'head'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search departments…')->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('parent')->placeholder('Top level'), TextColumn::make('head')->searchable(), BadgeColumn::make('status')->colors(['active' => 'success', 'archived' => 'gray']),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/departments/{id}')->method('get'), Action::make('edit')->url('/admin/departments/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/departments/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save department')->schema([Section::make('department')->label('Department details')->schema([Grid::make(2)->schema([
            TextInput::make('name')->required()->autofocus(), TextInput::make('parent')->label('Parent department'), TextInput::make('head')->label('Department head'), Select::make('status')->options(['active' => 'Active', 'archived' => 'Archived'])->required(),
        ])])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([TextEntry::make('name')->label('Department'), TextEntry::make('parent')->placeholder('Top level'), TextEntry::make('head'), TextEntry::make('status')->badge()->color('success')]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => DepartmentList::route('/'), 'create' => DepartmentCreate::route('/create'), 'view' => DepartmentView::route('/{record}'), 'edit' => DepartmentEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
