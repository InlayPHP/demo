<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Models\Shop\Brand;
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

final class BrandResource extends Resource
{
    protected static string $model = Brand::class;

    protected static ?string $label = 'Brand';

    protected static ?string $pluralLabel = 'Brands';

    protected static ?string $navigationIcon = 'badge';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 40;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'slug', 'website'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table->searchPlaceholder('Search brands…')->reorderable('sort')->columns([
            TextColumn::make('name')->searchable()->sortable(), TextColumn::make('slug')->copyable()->sortable(), BadgeColumn::make('status')->colors(['active' => 'success', 'inactive' => 'gray']), TextColumn::make('website')->url(fn (string $state): string => $state)->openUrlInNewTab(),
        ])->actions([
            Action::make('view')->label('View')->url('/admin/brands/{id}')->method('get'), Action::make('edit')->url('/admin/brands/{id}/edit')->method('get'), Action::make('delete')->color('danger')->url('/admin/brands/{id}')->method('delete')->requiresConfirmation(),
        ])->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save brand')->schema([Section::make('brand')->label('Brand details')->schema([
            Grid::make(2)->schema([TextInput::make('name')->required()->autofocus(), TextInput::make('slug')->required(), Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->required(), TextInput::make('website')->url(), TextInput::make('sort')->numeric()->required()]),
        ])]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([TextEntry::make('name')->label('Brand'), TextEntry::make('slug')->copyable(), TextEntry::make('status')->badge()->color('success'), TextEntry::make('website')->url(), TextEntry::make('sort')->numeric()]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return ['index' => BrandList::route('/'), 'create' => BrandCreate::route('/create'), 'view' => BrandView::route('/{record}'), 'edit' => BrandEdit::route('/{record}/edit')];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
