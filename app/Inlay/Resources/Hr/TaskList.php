<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericListPage;

final class TaskList extends GenericListPage
{
    protected static string $resource = TaskResource::class;

    protected int $perPage = 10;
}
