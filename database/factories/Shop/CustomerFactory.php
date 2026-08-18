<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
final class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->optional()->phoneNumber(),
            'status' => fake()->randomElement(['active', 'active', 'inactive']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
