<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Actions\ShopActions;
use App\Inlay\RelationManagers\Shop\ProductsRelationManager;
use App\Models\Shop\ProductCategory;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Forms\Fields\Placeholder;
use Inlay\Forms\Fields\RichEditor;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Fields\Toggle;
use Inlay\Forms\Form;
use Inlay\Forms\Support\Set;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Grid;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Actions\DeleteBulkAction;
use Inlay\Tables\Columns\IconColumn;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Table;

final class ProductCategoryResource extends Resource
{
    protected static string $model = ProductCategory::class;

    protected static ?string $label = 'Category';

    protected static ?string $pluralLabel = 'Categories';

    protected static ?string $navigationIcon = 'tag';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 4;

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                Action::make('import')
                    ->label('Import')
                    ->icon('upload')
                    ->url('/admin/shop/categories/import')
                    ->method('get'),
            ])
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('parent.name')->searchable()->sortable(),
                IconColumn::make('is_visible')->label('Visibility')->boolean()->sortable(),
                TextColumn::make('updated_at')->label('Last modified at')->date()->sortable(),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('toggle_visibility')
                        ->link()
                        ->icon('eye')
                        ->color('gray')
                        ->label('Toggle visibility')
                        ->authorizeUsing(ShopActions::allow())
                        ->action(fn (ProductCategory $record) => $record->update(['is_visible' => ! $record->is_visible])),
                    Action::make('edit')->label('Edit')->url('/admin/product-categories/{id}/edit')->method('get')->icon('pencil'),                ])
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
        return $form->columns(3)->schema([
            Section::make('category')
                ->columnSpan(fn (mixed $record): int => $record instanceof Model ? 2 : 3)
                ->schema([
                    Grid::make()->schema([
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
                            ->unique('shop_product_categories', 'slug', ignoreRecord: true),
                    ]),
                    Select::make('parent_id')
                        ->relationship('parent', 'name', fn (Builder $query): Builder => $query->whereNull('parent_id'))
                        ->searchable()
                        ->placeholder('Select parent category'),
                    Toggle::make('is_visible')->label('Visibility')->default(true),
                    RichEditor::make('description'),
                ]),
            Section::make('timestamps')
                ->columnSpan(['lg' => 1])
                ->hidden(fn (mixed $record): bool => ! $record instanceof Model)
                ->schema([
                    Placeholder::make('created_at')->content(fn (mixed $record): ?string => $record instanceof Model ? $record->created_at?->diffForHumans() : null),
                    Placeholder::make('updated_at')->label('Last modified at')->content(fn (mixed $record): ?string => $record instanceof Model ? $record->updated_at?->diffForHumans() : null),
                ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Category'),
            TextEntry::make('parent.name')->label('Parent')->placeholder('Top-level category'),
            TextEntry::make('is_visible')->label('Visibility')->badge()->color('success'),
            TextEntry::make('description')->columnSpanFull()->prose(),
        ]);
    }

    public static function getRelations(): array
    {
        return [ProductsRelationManager::class];
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => ProductCategoryList::route('/'),
            'create' => ProductCategoryCreate::route('/create'),
            'view' => ProductCategoryView::route('/{record}'),
            'edit' => ProductCategoryEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
