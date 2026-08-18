<?php

declare(strict_types=1);

namespace Database\Factories\Shop;

use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
final class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'number' => 'OR-'.fake()->unique()->numerify('######'),
            'customer_id' => Customer::factory(),
            'status' => fake()->randomElement(['paid', 'paid', 'pending', 'shipped', 'refunded', 'cancelled']),
            'payment_method' => fake()->randomElement(['card', 'bank_transfer', 'cash']),
            'total' => fake()->randomFloat(2, 48, 2400),
            'placed_at' => fake()->dateTimeBetween('-18 months', 'now'),
            'notes' => fake()->optional()->sentence(),
            'items' => [
                ['name' => fake()->words(2, true), 'quantity' => fake()->numberBetween(1, 4), 'price' => fake()->randomFloat(2, 20, 240)],
            ],
        ];
    }
}
