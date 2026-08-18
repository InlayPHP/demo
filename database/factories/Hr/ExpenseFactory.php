<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\Expense;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Expense> */
final class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    public function definition(): array
    {
        $submitted = fake()->dateTimeBetween('-10 months', 'now');

        return [
            'employee' => fake()->name(),
            'category' => fake()->randomElement(['Travel', 'Equipment', 'Meals', 'Training', 'Software']),
            'status' => fake()->randomElement(['submitted', 'approved', 'rejected', 'reimbursed']),
            'amount' => fake()->randomFloat(2, 24, 2400),
            'submitted_at' => $submitted,
            'approved_at' => fake()->optional(0.55)->dateTimeBetween($submitted, 'now'),
            'line_items' => [
                ['description' => fake()->sentence(3), 'amount' => fake()->randomFloat(2, 12, 800)],
            ],
        ];
    }
}
