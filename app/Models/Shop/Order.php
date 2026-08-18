<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Database\Factories\Shop\OrderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['number', 'customer_id', 'status', 'payment_method', 'total', 'placed_at', 'notes', 'items'])]
final class Order extends Model
{
    protected $table = 'shop_orders';

    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    /** @return BelongsTo<Customer, $this> */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'items' => 'array',
        ];
    }
}
