<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericListPage;
use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Builder;
use Inlay\Resources\Pages\PageTab;

final class OrderList extends GenericListPage
{
    protected static string $resource = OrderResource::class;

    protected int $perPage = 10;

    protected function tabs(): array
    {
        return [
            PageTab::make('all')->label('All orders')->badge(fn (): int => Order::query()->count())->default(),
            PageTab::make('pending')->label('Pending')->badge(fn (): int => Order::query()->where('status', 'pending')->count())->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'pending')),
            PageTab::make('paid')->label('Paid')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'paid')),
            PageTab::make('shipped')->label('Shipped')->modifyQueryUsing(fn (Builder $query): Builder => $query->where('status', 'shipped')),
        ];
    }
}
