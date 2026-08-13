<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Blog>
 */
final class BlogFactory extends Factory
{
    protected $model = Blog::class;

    public function definition(): array
    {
        $title = fake()->unique()->sentence(4);

        return [
            'title' => $title,
            'slug' => Str::slug($title),
            'status' => fake()->randomElement(['draft', 'published']),
            'excerpt' => fake()->paragraph(),
            'body' => fake()->paragraphs(3, true),
            'published_at' => fake()->optional()->dateTimeBetween('-30 days', 'now'),
            'featured' => fake()->boolean(20),
        ];
    }
}
