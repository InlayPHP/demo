<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Blog;

use App\Inlay\Resources\Pages\GenericCreatePage;

final class AuthorCreate extends GenericCreatePage
{
    protected static string $resource = AuthorResource::class;
}
