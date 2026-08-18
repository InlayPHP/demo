<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use Inlay\Resources\Pages\EditRecord;

final class EditBlog extends EditRecord
{
    protected static string $resource = BlogResource::class;

    protected static string $component = 'inlay/resource/form';
}
