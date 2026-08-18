<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use App\Inlay\Resources\Pages\GenericViewPage;

final class ViewBlog extends GenericViewPage
{
    protected static string $resource = BlogResource::class;
}
