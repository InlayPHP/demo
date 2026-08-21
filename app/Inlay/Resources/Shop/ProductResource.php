<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Models\Shop\Product;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\Toggle;
use Inlay\Forms\Form;
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

final class ProductResource extends Resource
{
    protected static string $model = Product::class;

    protected static ?string $label = 'Product';

    protected static ?string $pluralLabel = 'Products';

    protected static ?string $navigationIcon = 'package';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 10;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'sku', 'description'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search products…')
            ->filters([
                SelectFilter::make('status')->label('Status')->options([
                    'active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived',
                ]),
            ])
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->limit(46),
                TextColumn::make('sku')->label('SKU')->copyable()->sortable(),
                BadgeColumn::make('status')->colors(['active' => 'success', 'draft' => 'warning', 'archived' => 'gray']),
                TextColumn::make('price')->money('USD')->sortable()->alignment('right'),
                TextColumn::make('stock')->numeric()->sortable()->alignment('right'),
            ])
            ->actions([
                Action::make('view')->label('View')->url('/admin/products/{id}')->method('get'),
                Action::make('edit')->label('Edit')->url('/admin/products/{id}/edit')->method('get'),
                Action::make('delete')->label('Delete')->color('danger')->url('/admin/products/{id}')->method('delete')->requiresConfirmation(),
            ])
            ->paginationPageOptions([10, 25, 50])
            ->emptyState('No products found', 'Create the first product for the showcase.');
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save product')->schema([
            Section::make('product')->label('Product details')->description('A PHP-defined product form with validation, badges, and stock data.')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->autofocus()->maxLength(255),
                    TextInput::make('sku')->label('SKU')->required()->maxLength(80),
                    Select::make('status')->options(['active' => 'Active', 'draft' => 'Draft', 'archived' => 'Archived'])->required(),
                    TextInput::make('price')->numeric()->prefix('$')->required(),
                    TextInput::make('stock')->numeric()->required(),
                    Toggle::make('featured')->label('Featured product'),
                ]),
                Textarea::make('description')->rows(5)->maxLength(2000),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Product name'),
            TextEntry::make('sku')->label('SKU')->copyable(),
            TextEntry::make('status')->badge()->color('success'),
            TextEntry::make('price')->money('USD'),
            TextEntry::make('stock')->numeric(),
            TextEntry::make('description')->prose()->columnSpanFull(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => ProductList::route('/'),
            'create' => ProductCreate::route('/create'),
            'view' => ProductView::route('/{record}'),
            'edit' => ProductEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
