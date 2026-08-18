<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<ProductCategory> */
final class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->sentence(2);

        return ['name' => Str::title($name), 'slug' => Str::slug($name), 'description' => fake()->optional()->sentence()];
    }
}
