<?php

declare(strict_types=1);

namespace Database\Factories\Blog;

use App\Models\Blog\Author;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Author> */
final class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'bio' => fake()->paragraph(),
            'active' => fake()->boolean(90),
        ];
    }
}
