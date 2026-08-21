<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Actions\ShopActions;
use App\Inlay\Exports\Shop\BrandExporter;
use App\Inlay\RelationManagers\Shop\AddressesRelationManager;
use App\Inlay\RelationManagers\Shop\ProductsRelationManager;
use App\Models\Shop\Brand;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Forms\Fields\Placeholder;
use Inlay\Forms\Fields\RichEditor;
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

final class BrandResource extends Resource
{
    protected static string $model = Brand::class;

    protected static ?string $label = 'Brand';

    protected static ?string $pluralLabel = 'Brands';

    protected static ?string $navigationIcon = 'bookmark';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 3;

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->headerActions([
                BrandExporter::exportAction(),
            ])
            ->columns([
                TextColumn::make('name')->searchable()->sortable()->weight('medium'),
                TextColumn::make('website')
                    ->searchable()
                    ->sortable()
                    // The column links to the brand's site when a website is
                    // set; brands without one render plain text (the closure is
                    // null-safe, so a null state can never crash the table).
                    ->url(fn (?string $state): ?string => $state)
                    ->openUrlInNewTab(),
                IconColumn::make('is_visible')->label('Visibility')->boolean()->sortable(),
                TextColumn::make('updated_at')->label('Last modified at')->date()->sortable(),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('toggle_visibility')
                        ->icon('eye')
                        ->color('gray')
                        ->tooltip('Toggle brand visibility')
                        ->authorizeUsing(ShopActions::allow())
                        ->action(fn (Brand $record) => $record->update(['is_visible' => ! $record->is_visible])),
                    Action::make('edit')->label('Edit')->url('/admin/brands/{id}/edit')->method('get')->icon('pencil'),                ])
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
            Section::make('brand')
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
                            ->unique('shop_brands', 'slug', ignoreRecord: true),
                    ]),
                    TextInput::make('website')->required()->maxLength(255)->url(),
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
            TextEntry::make('name')->label('Brand'),
            TextEntry::make('website')->url()->openUrlInNewTab(),
            TextEntry::make('is_visible')->label('Visibility')->badge()->color('success'),
            TextEntry::make('description')->columnSpanFull()->prose(),
        ]);
    }

    public static function getRelations(): array
    {
        return [ProductsRelationManager::class, AddressesRelationManager::class];
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => BrandList::route('/'),
            'create' => BrandCreate::route('/create'),
            'view' => BrandView::route('/{record}'),
            'edit' => BrandEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
