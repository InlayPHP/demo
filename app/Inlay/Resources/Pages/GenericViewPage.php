<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Pages;

use Inlay\Resources\Pages\ViewRecord;

abstract class GenericViewPage extends ViewRecord
{
    protected static string $component = 'inlay/resource/view';
}
