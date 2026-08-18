<?php

declare(strict_types=1);

namespace App\Models\Blog;

use Database\Factories\Blog\AuthorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'email', 'bio', 'active'])]
final class Author extends Model
{
    protected $table = 'blog_authors';

    /** @use HasFactory<AuthorFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['active' => 'boolean'];
    }
}
