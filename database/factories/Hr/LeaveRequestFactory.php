<?php

declare(strict_types=1);

namespace Database\Factories\Hr;

use App\Models\Hr\LeaveRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<LeaveRequest> */
final class LeaveRequestFactory extends Factory
{
    protected $model = LeaveRequest::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-4 months', '+3 months');

        return [
            'employee' => fake()->name(),
            'type' => fake()->randomElement(['annual', 'sick', 'parental', 'unpaid']),
            'status' => fake()->randomElement(['pending', 'approved', 'rejected']),
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+'.fake()->numberBetween(1, 7).' days'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
