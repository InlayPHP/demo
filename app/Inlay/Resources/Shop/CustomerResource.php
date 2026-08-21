<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Actions\ShopActions;
use App\Inlay\RelationManagers\Shop\AddressesRelationManager;
use App\Inlay\RelationManagers\Shop\CustomerPaymentsRelationManager;
use App\Models\Shop\Customer;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Actions\ActionGroup;
use Inlay\Forms\Fields\DatePicker;
use Inlay\Forms\Fields\Placeholder;
use Inlay\Forms\Fields\RichEditor;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Infolists\Entries\TextEntry;
use Inlay\Infolists\Infolist;
use Inlay\Notifications\Notification;
use Inlay\Resources\Resource;
use Inlay\Resources\ResourceOperation;
use Inlay\Schemas\Components\Section;
use Inlay\Tables\Actions\DeleteBulkAction;
use Inlay\Tables\Columns\TextColumn;
use Inlay\Tables\Filters\TrashedFilter;
use Inlay\Tables\Table;

final class CustomerResource extends Resource
{
    protected static string $model = Customer::class;

    protected static bool $softDeletes = true;

    protected static ?string $label = 'Customer';

    protected static ?string $pluralLabel = 'Customers';

    protected static ?string $navigationIcon = 'users';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 2;

    public static function globallySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'name';
    }

    protected static function modifyEloquentQuery(Builder $query): Builder
    {
        return $query->with('addresses');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->filters([
                TrashedFilter::make(),
            ])
            ->columns([
                TextColumn::make('name')->searchable(isIndividual: true, isGlobal: false)->sortable()->weight('medium'),
                TextColumn::make('email')->label('Email address')->searchable(isIndividual: true, isGlobal: false)->sortable(),
                TextColumn::make('country'),
                TextColumn::make('phone')->searchable()->sortable(),
            ])
            ->actions([
                ActionGroup::make('row_actions', [
                    Action::make('send_email')
                        ->icon('mail')
                        ->color('info')
                        ->modalWidth('lg')
                        ->modalSubmitAction(Action::make('submit')->label('Send'))
                        ->fillForm(fn (Customer $record): array => ['to' => $record->email])
                        ->form([
                            TextInput::make('to')->email()->disabled()->dehydrated(),
                            TextInput::make('subject')->required(),
                            RichEditor::make('body')->required()->columnSpanFull(),
                        ])
                        ->authorizeUsing(ShopActions::allow())
                        ->action(function (Customer $record): void {
                            Notification::make("Email sent to {$record->name}")->success()->send();
                        }),
                    Action::make('edit')->label('Edit')->url('/admin/customers/{id}/edit')->method('get')->icon('pencil'),                ])
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
            Section::make('profile')
                ->columns(2)
                ->columnSpan(fn (mixed $record): int => $record instanceof Model ? 2 : 3)
                ->schema([
                    TextInput::make('name')->maxLength(255)->required(),
                    TextInput::make('email')->label('Email address')->required()->email()->maxLength(255)->unique('shop_customers', 'email', ignoreRecord: true),
                    TextInput::make('phone')->maxLength(255),
                    DatePicker::make('birthday')->maxDate('today'),
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
            TextEntry::make('name')->label('Customer'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('phone')->placeholder('Not provided'),
            TextEntry::make('birthday')->date(),
        ]);
    }

    public static function getRelations(): array
    {
        return [AddressesRelationManager::class, CustomerPaymentsRelationManager::class];
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
