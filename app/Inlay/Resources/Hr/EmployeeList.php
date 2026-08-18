<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericListPage;

final class EmployeeList extends GenericListPage
{
    protected static string $resource = EmployeeResource::class;

    protected int $perPage = 10;
}
