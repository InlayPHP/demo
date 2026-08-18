<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Product> */
final class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(3);

        return [
            'name' => Str::title($name),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####??')),
            'status' => fake()->randomElement(['active', 'active', 'draft', 'archived']),
            'price' => fake()->randomFloat(2, 18, 480),
            'stock' => fake()->numberBetween(0, 160),
            'description' => fake()->paragraph(),
            'featured' => fake()->boolean(18),
        ];
    }
}
