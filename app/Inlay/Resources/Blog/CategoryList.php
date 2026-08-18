<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Inlay\Resources\Pages\GenericListPage;

final class CategoryList extends GenericListPage
{
    protected static string $resource = CategoryResource::class;

    protected int $perPage = 10;
}
