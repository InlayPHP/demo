<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Inlay\Resources\Pages\GenericViewPage;

final class AuthorView extends GenericViewPage
{
    protected static string $resource = AuthorResource::class;
}
