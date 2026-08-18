<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Inlay\Resources\Pages\GenericListPage;

final class AuthorList extends GenericListPage
{
    protected static string $resource = AuthorResource::class;

    protected int $perPage = 10;
}
