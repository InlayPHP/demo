<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Database\Factories\Shop\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'sku', 'status', 'price', 'stock', 'description', 'featured'])]
final class Product extends Model
{
    protected $table = 'shop_products';

    /** @use HasFactory<ProductFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'stock' => 'integer',
            'featured' => 'boolean',
        ];
    }
}
