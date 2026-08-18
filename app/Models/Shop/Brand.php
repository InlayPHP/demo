<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Database\Factories\Shop\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'status', 'website', 'sort'])]
final class Brand extends Model
{
    protected $table = 'shop_brands';

    /** @use HasFactory<BrandFactory> */
    use HasFactory;
}
