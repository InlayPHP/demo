<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Models\Shop\ProductCategory;
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

final class ProductCategoryResource extends Resource
{
    protected static string $model = ProductCategory::class;

    protected static ?string $label = 'Product category';

    protected static ?string $pluralLabel = 'Product categories';

    protected static ?string $navigationIcon = 'folder-tree';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 50;

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
        return $table->searchPlaceholder('Search product categories…')->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('slug')->copyable()->sortable(), TextColumn::make('description')->limit(64)->wrap(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/product-categories/{id}')->method('get'), Action::make('edit')->url('/admin/product-categories/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/product-categories/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save category')->schema([Section::make('category')->label('Product category')->schema([TextInput::make('name')->required()->autofocus(), TextInput::make('slug')->required(), Textarea::make('description')->rows(5)])]);
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
        return ['index' => ProductCategoryList::route('/'), 'create' => ProductCategoryCreate::route('/create'), 'view' => ProductCategoryView::route('/{record}'), 'edit' => ProductCategoryEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
