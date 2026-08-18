<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Inlay\Resources\Pages\GenericCreatePage;

final class CategoryCreate extends GenericCreatePage
{
    protected static string $resource = CategoryResource::class;
}
