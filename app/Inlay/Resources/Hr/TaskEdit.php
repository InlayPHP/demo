<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericEditPage;

final class TaskEdit extends GenericEditPage
{
    protected static string $resource = TaskResource::class;
}
