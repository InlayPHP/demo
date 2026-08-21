<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Actions\ShopActions;
use App\Inlay\RelationManagers\Shop\CommentsRelationManager;
use App\Inlay\Widgets\Shop\ProductStats;
use App\Models\Shop\Product;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Forms\Fields\Checkbox;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\FileUpload;
use Inlay\Forms\Fields\RichEditor;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\Toggle;
use Inlay\Forms\Form;
use Inlay\Forms\Support\Set;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Media\Models\MediaAsset;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Group;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Actions\DeleteBulkAction;
use Inlay\Tables\Columns\IconColumn;
use Inlay\Tables\Columns\ImageColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Filters\QueryBuilder;
use Inlay\Tables\Filters\QueryBuilder\BooleanConstraint;
use Inlay\Tables\Filters\QueryBuilder\DateConstraint;
use Inlay\Tables\Filters\QueryBuilder\NumberConstraint;
use Inlay\Tables\Filters\QueryBuilder\TextConstraint;
use Inlay\Tables\Table;

final class ProductResource extends Resource
{
    protected static string $model = Product::class;

    protected static ?string $label = 'Product';

    protected static ?string $pluralLabel = 'Products';

    protected static ?string $navigationIcon = 'zap';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 0;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'sku'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                QueryBuilder::make('product-filters')->constraints([
                    TextConstraint::make('name'),
                    TextConstraint::make('slug'),
                    TextConstraint::make('sku')->label('SKU (Stock Keeping Unit)'),
                    TextConstraint::make('barcode')->label('Barcode (ISBN, UPC, GTIN, etc.)'),
                    TextConstraint::make('description'),
                    NumberConstraint::make('old_price')->label('Compare at price'),
                    NumberConstraint::make('price'),
                    NumberConstraint::make('cost')->label('Cost per item'),
                    NumberConstraint::make('qty')->label('Quantity'),
                    NumberConstraint::make('security_stock'),
                    BooleanConstraint::make('is_visible')->label('Visibility'),
                    BooleanConstraint::make('featured'),
                    BooleanConstraint::make('backorder'),
                    BooleanConstraint::make('requires_shipping'),
                    DateConstraint::make('published_at')->label('Publishing date'),
                ]),
            ])
            ->filtersLayout('above-content-collapsible')
            ->deferFilters()
            ->columns([
                ImageColumn::make('image')
                    ->state(function (array $record): ?array {
                        $paths = collect($record['image'] ?? [])->filter()->values()->all();
                        $assets = MediaAsset::query()->whereIn('path', $paths)->pluck('id', 'path');

                        return collect($paths)
                            ->filter(fn (string $path): bool => $assets->has($path))
                            ->map(fn (string $path): string => URL::temporarySignedRoute(
                                'inlay.admin.media.assets.delivery',
                                now()->addMinutes(30),
                                ['asset' => $assets->get($path)],
                            ))
                            ->values()
                            ->all();
                    })
                    ->size(40),
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('brand.name')->searchable()->sortable()->toggleable(),
                IconColumn::make('is_visible')->label('Visibility')->boolean()->sortable()->toggleable(),
                TextColumn::make('price')->searchable()->sortable(),
                TextColumn::make('sku')->label('SKU')->searchable()->sortable()->toggleable(),
                TextColumn::make('qty')->label('Quantity')->searchable()->sortable()->toggleable(),
                TextColumn::make('security_stock')->searchable()->sortable()->toggleable(true, true),
                TextColumn::make('published_at')->label('Publishing date')->date()->sortable()->toggleable(true, true),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('edit')->label('Edit')->url('/admin/products/{id}/edit')->method('get')->icon('pencil'),
                    Action::make('toggle_visibility')
                        ->icon('eye')
                        ->label('Toggle visibility')
                        ->color('gray')
                        ->authorizeUsing(ShopActions::allow())
                        ->action(fn (Product $record) => $record->update(['is_visible' => ! $record->is_visible])),
                    Action::make('adjust_price')
                        ->icon('dollar-sign')
                        ->color('warning')
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('submit')->label('Save'))
                        ->fillForm(fn (Product $record): array => ['price' => $record->price, 'old_price' => $record->old_price])
                        ->form([
                            TextInput::make('price')->numeric()->prefix('$')->minValue(0)->maxValue(99999999.99)->required(),
                            TextInput::make('old_price')->label('Compare at price')->numeric()->prefix('$')->minValue(0)->maxValue(99999999.99),
                        ])
                        ->authorizeUsing(ShopActions::allow())
                        ->action(fn (Product $record, array $data) => $record->update($data)),
                    Action::make('adjust_stock')
                        ->icon('boxes')
                        ->color('info')
                        ->modalWidth('md')
                        ->modalSubmitAction(Action::make('submit')->label('Save'))
                        ->fillForm(fn (Product $record): array => ['qty' => $record->qty])
                        ->form([
                            TextInput::make('qty')->label('Quantity')->integer()->required(),
                        ])
                        ->action(fn (Product $record, array $data) => $record->update($data)),
                    Action::make('delete')
                        ->label('Delete')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->authorizeUsing(ShopActions::allow())
                        ->action(function (): void {
                            Notification::make('Now, now, don\'t be cheeky, leave some records for others to play with!')->warning()->send();
                        }),                ])
                    ->icon('ellipsis-vertical')
                    ->iconButton()
                    ->tooltip('Row actions')
                    ->dropdownPlacement('left-start'),
            ])
            ->bulkActions([
                DeleteBulkAction::make('delete')->authorizeUsing(ShopActions::allow())->action(function (): void {
                    Notification::make('Now, now, don\'t be cheeky, leave some records for others to play with!')->warning()->send();
                }),
            ])
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->columns(3)->schema(self::formComponents());
    }

    /** @return list<object> */
    public static function formComponents(bool $hideBrand = false): array
    {
        return [
            Group::make('details')
                ->columnSpan(['lg' => 2])
                ->schema([
                    Section::make('product')
                        ->columns(2)
                        ->schema([
                            TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (string $operation, mixed $state, Set $set): void {
                                    if ($operation !== 'create') {
                                        return;
                                    }
                                    $set('slug', Str::slug((string) $state));
                                }),
                            TextInput::make('slug')
                                ->readOnly()
                                ->dehydrated()
                                ->required()
                                ->maxLength(255)
                                ->unique('shop_products', 'slug', ignoreRecord: true),
                            RichEditor::make('description')->columnSpan('full'),
                        ]),
                    Section::make('images')
                        ->label('Images')
                        ->collapsible()
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->multiple()
                                ->maxFiles(5)
                                ->reorderable()
                                ->acceptedFileTypes('image/jpeg')
                                ->hiddenLabel()
                                ->disk((string) config('media.disk'))
                                ->directory((string) config('media.directory')),
                        ]),
                    Section::make('pricing')
                        ->label('Pricing')
                        ->columns(2)
                        ->schema([
                            TextInput::make('price')->numeric()->minValue(0)->maxValue(99999999.99)->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')->required(),
                            TextInput::make('old_price')->label('Compare at price')->numeric()->minValue(0)->maxValue(99999999.99)->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')->required(),
                            TextInput::make('cost')->label('Cost per item')->helperText('Customers won\'t see this price.')->numeric()->minValue(0)->maxValue(99999999.99)->rules('regex:/^\d{1,6}(\.\d{0,2})?$/')->required(),
                        ]),
                    Section::make('inventory')
                        ->label('Inventory')
                        ->columns(2)
                        ->schema([
                            TextInput::make('sku')->label('SKU (Stock Keeping Unit)')->unique('shop_products', 'sku', ignoreRecord: true)->maxLength(255)->required(),
                            TextInput::make('barcode')->label('Barcode (ISBN, UPC, GTIN, etc.)')->unique('shop_products', 'barcode', ignoreRecord: true)->maxLength(255)->required(),
                            TextInput::make('qty')->label('Quantity')->numeric()->minValue(0)->maxValue(18446744073709551615)->integer()->required(),
                            TextInput::make('security_stock')->helperText('The safety stock is the limit stock for your products which alerts you if the product stock will soon be out of stock.')->numeric()->minValue(0)->maxValue(18446744073709551615)->integer()->required(),
                        ]),
                    Section::make('shipping')
                        ->label('Shipping')
                        ->columns(2)
                        ->schema([
                            Checkbox::make('backorder')->label('This product can be returned'),
                            Checkbox::make('requires_shipping')->label('This product will be shipped'),
                        ]),
                ]),
            Group::make('sidebar')
                ->columnSpan(['lg' => 1])
                ->schema([
                    Section::make('status')
                        ->label('Status')
                        ->schema([
                            Toggle::make('is_visible')->label('Visibility')->helperText('This product will be hidden from all sales channels.')->default(true),
                            DatePicker::make('published_at')->label('Publishing date')->default(now())->required(),
                        ]),
                    Section::make('associations')
                        ->label('Associations')
                        ->schema([
                            Select::make('brand_id')->relationship('brand', 'name')->searchable()->hidden($hideBrand),
                            Select::make('productCategories')->relationship('productCategories', 'name')->multiple()->required(),
                        ]),
                ]),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Product name'),
            TextEntry::make('sku')->label('SKU')->copyable(),
            TextEntry::make('brand.name')->label('Brand')->placeholder('No brand'),
            TextEntry::make('price')->money('USD'),
            TextEntry::make('qty')->label('Quantity')->numeric(),
            TextEntry::make('is_visible')->label('Visibility')->badge()->color('success'),
            TextEntry::make('description')->prose()->columnSpanFull(),
        ]);
    }

    public static function getRelations(): array
    {
        return [CommentsRelationManager::class];
    }

    public static function widgets(): array
    {
        return [ProductStats::class];
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
