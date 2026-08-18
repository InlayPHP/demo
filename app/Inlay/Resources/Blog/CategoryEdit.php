<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Inlay\Resources\Pages\GenericEditPage;

final class CategoryEdit extends GenericEditPage
{
    protected static string $resource = CategoryResource::class;
}
