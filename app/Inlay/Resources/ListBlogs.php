<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use Inlay\Resources\Pages\ListRecords;

final class ListBlogs extends ListRecords
{
    protected static string $resource = BlogResource::class;

    protected static string $component = 'inlay/resource/index';

    protected int $perPage = 10;
}
