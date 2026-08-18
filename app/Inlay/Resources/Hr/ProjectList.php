<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericListPage;

final class ProjectList extends GenericListPage
{
    protected static string $resource = ProjectResource::class;

    protected int $perPage = 10;
}
