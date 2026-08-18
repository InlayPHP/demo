<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericListPage;

final class TimesheetList extends GenericListPage
{
    protected static string $resource = TimesheetResource::class;

    protected int $perPage = 10;
}
