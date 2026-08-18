<?php

declare(strict_types=1);

namespace App\Models\Shop;

use Database\Factories\Shop\CustomerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'phone', 'status', 'notes'])]
final class Customer extends Model
{
    protected $table = 'shop_customers';

    /** @use HasFactory<CustomerFactory> */
    use HasFactory;
}
