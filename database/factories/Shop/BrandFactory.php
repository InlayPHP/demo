<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<Brand> */
final class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $name = fake()->unique()->company();

        return ['name' => $name, 'slug' => Str::slug($name), 'status' => fake()->randomElement(['active', 'active', 'inactive']), 'website' => fake()->optional()->url(), 'sort' => 0];
    }
}
