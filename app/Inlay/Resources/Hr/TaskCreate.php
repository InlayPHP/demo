<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericCreatePage;

final class TaskCreate extends GenericCreatePage
{
    protected static string $resource = TaskResource::class;
}
