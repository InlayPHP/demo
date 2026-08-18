<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Models\Blog\Category;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class CategoryResource extends Resource
{
    protected static string $model = Category::class;

    protected static ?string $label = 'Category';

    protected static ?string $pluralLabel = 'Categories';

    protected static ?string $navigationIcon = 'tags';

    protected static ?string $navigationGroup = 'Blog';

    protected static int $navigationSort = 20;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search categories…')->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('slug')->copyable()->sortable(), TextColumn::make('description')->limit(64)->wrap(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/categories/{id}')->method('get'), Action::make('edit')->url('/admin/categories/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/categories/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save category')->schema([Section::make('category')->label('Category details')->schema([
            TextInput::make('name')->required()->autofocus(), TextInput::make('slug')->required()->helperText('Lowercase URL slug, for example tutorials.'), Textarea::make('description')->rows(5),
        ])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([TextEntry::make('name')->label('Category'), TextEntry::make('slug')->copyable(), TextEntry::make('description')->prose()]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => CategoryList::route('/'), 'create' => CategoryCreate::route('/create'), 'view' => CategoryView::route('/{record}'), 'edit' => CategoryEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
