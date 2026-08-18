<?php

declare(strict_types=1);

namespace App\Models\Blog;

use Database\Factories\Blog\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'slug', 'description'])]
final class Category extends Model
{
    protected $table = 'blog_categories';

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;
}
