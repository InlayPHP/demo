<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericViewPage;

final class TimesheetView extends GenericViewPage
{
    protected static string $resource = TimesheetResource::class;
}
