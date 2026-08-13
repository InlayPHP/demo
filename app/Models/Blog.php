<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\BlogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $status
 * @property string|null $excerpt
 * @property string $body
 * @property Carbon|null $published_at
 * @property bool $featured
 */
#[Fillable(['title', 'slug', 'status', 'excerpt', 'body', 'published_at', 'featured'])]
class Blog extends Model
{
    /** @use HasFactory<BlogFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'featured' => 'boolean',
        ];
    }
}
