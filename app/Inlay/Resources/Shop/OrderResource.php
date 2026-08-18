<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Validation\ShowcaseRules;
use Illuminate\Database\Eloquent\Model;
use Inlay\Actions\Action;
use Inlay\Forms\Fields\Repeater;
use Inlay\Forms\Fields\Select;
use Inlay\Forms\Fields\Textarea;
use Inlay\Forms\Fields\TextInput;
use Inlay\Forms\Form;
use Inlay\Forms\Repeater\TableColumn;
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

final class OrderResource extends Resource
{
    protected static string $model = Order::class;

    protected static ?string $label = 'Order';

    protected static ?string $pluralLabel = 'Orders';

    protected static ?string $navigationIcon = 'shopping-bag';

    protected static ?string $navigationGroup = 'Shop';

    protected static int $navigationSort = 30;

    public static function globallySearchableAttributes(): array
    {
        return ['number', 'status', 'payment_method'];
    }

    public static function recordTitleAttribute(): string
    {
        return 'number';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->searchPlaceholder('Search order number…')
            ->filters([SelectFilter::make('status')->options([
                'pending' => 'Pending', 'paid' => 'Paid', 'shipped' => 'Shipped', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled',
            ])])
            ->columns([
                TextColumn::make('number')->label('Order')->searchable()->sortable()->copyable(),
                TextColumn::make('customer.name')->label('Customer')->searchable()->sortable()->placeholder('Guest'),
                BadgeColumn::make('status')->colors(['pending' => 'warning', 'paid' => 'success', 'shipped' => 'info', 'refunded' => 'gray', 'cancelled' => 'danger']),
                TextColumn::make('total')->money('USD')->sortable()->alignment('right'),
                TextColumn::make('placed_at')->dateTime('M j, Y H:i')->sortable(),
            ])
            ->actions([
                Action::make('view')->label('View')->url('/admin/orders/{id}')->method('get'),
                Action::make('edit')->url('/admin/orders/{id}/edit')->method('get'),
                Action::make('delete')->color('danger')->url('/admin/orders/{id}')->method('delete')->requiresConfirmation(),
            ])
            ->paginationPageOptions([10, 25, 50]);
    }

    public static function form(Form $form): Form
    {
        return $form->submitLabel('Save order')->schema([
            Section::make('order')->label('Order details')->schema([
                Grid::make(2)->schema([
                    TextInput::make('number')->label('Order number')->required()->maxLength(30),
                    Select::make('customer_id')->label('Customer')->options(fn (): array => Customer::query()->orderBy('name')->pluck('name', 'id')->all())->searchable()->placeholder('Guest checkout'),
                    Select::make('status')->options(['pending' => 'Pending', 'paid' => 'Paid', 'shipped' => 'Shipped', 'refunded' => 'Refunded', 'cancelled' => 'Cancelled'])->required(),
                    Select::make('payment_method')->options(['card' => 'Card', 'bank_transfer' => 'Bank transfer', 'cash' => 'Cash'])->required(),
                    TextInput::make('total')->numeric()->prefix('$')->required(),
                    TextInput::make('placed_at')->label('Placed at')->required()->helperText('Use a date/time accepted by Laravel.'),
                ]),
                Repeater::make('items')->label('Line items')->table([
                    TableColumn::make('Item'),
                    TableColumn::make('Qty'),
                    TableColumn::make('Price'),
                ])->schema([
                    TextInput::make('name')->required(),
                    TextInput::make('quantity')->numeric()->required(),
                    TextInput::make('price')->numeric()->prefix('$')->required(),
                ])->minItems(1)->addActionLabel('Add line item'),
                Textarea::make('notes')->rows(3),
            ]),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->columns(2)->schema([
            TextEntry::make('number')->label('Order')->copyable(),
            TextEntry::make('customer.name')->label('Customer')->placeholder('Guest checkout'),
            TextEntry::make('status')->badge()->color('success'),
            TextEntry::make('payment_method')->label('Payment method'),
            TextEntry::make('total')->money('USD'),
            TextEntry::make('placed_at')->dateTime('M j, Y H:i'),
            TextEntry::make('items')->label('Line items')->list()->bulleted()->columnSpanFull(),
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
            'index' => OrderList::route('/'),
            'create' => OrderCreate::route('/create'),
            'view' => OrderView::route('/{record}'),
            'edit' => OrderEdit::route('/{record}/edit'),
        ];
    }

    protected static function canAccess(ResourceOperation $operation, ?Model $record, mixed $user): bool
    {
        return $user !== null;
    }
}
