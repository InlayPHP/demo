<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use Inlay\Resources\Pages\CreateRecord;

final class CreateBlog extends CreateRecord
{
    protected static string $resource = BlogResource::class;

    protected static string $component = 'inlay/resource/form';
}
