<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Models\Shop\Customer;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
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
use Inlay\Tables\Filters\SelectFilter;
use Inlay\Tables\Table;

final class CustomerResource extends Resource
{
    protected static string $model = Customer::class;

    protected static ?string $label = 'Customer';

    protected static ?string $pluralLabel = 'Customers';

    protected static ?string $navigationIcon = 'users';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 20;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'email', 'phone'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search customers…')
            ->filters([SelectFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])])
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('phone')->placeholder('Not provided'),
                BadgeColumn::make('status')->colors(['active' => 'success', 'inactive' => 'gray']),
                TextColumn::make('created_at')->label('Joined')->date('M j, Y')->sortable(),
            ])
            ->actions([
                Action::make('view')->label('View')->url('/admin/customers/{id}')->method('get'),
                Action::make('edit')->url('/admin/customers/{id}/edit')->method('get'),
                Action::make('delete')->color('danger')->url('/admin/customers/{id}')->method('delete')->requiresConfirmation(),
            ])
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save customer')->schema([
            Section::make('profile')->label('Customer profile')->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')->required()->autofocus(),
                    TextInput::make('email')->email()->required(),
                    TextInput::make('phone')->tel()->telRegex('/^\\+?[0-9 ()-]{7,}$/'),
                    Select::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive'])->required(),
                ]),
                Textarea::make('notes')->rows(4)->helperText('Internal notes are visible to the shop team.'),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('name')->label('Customer'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('phone')->placeholder('Not provided'),
            TextEntry::make('status')->badge()->color('success'),
            TextEntry::make('notes')->columnSpanFull()->wrap(),
        ]);
    }

    public static function validation(): string
    {
        return ShowcaseRules::class;
    }

    public static function getPages(): array
    {
        return [
            'index' => CustomerList::route('/'),
            'create' => CustomerCreate::route('/create'),
            'view' => CustomerView::route('/{record}'),
            'edit' => CustomerEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
