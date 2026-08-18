<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Pages;

use Inlay\Resources\Pages\ListRecords;

abstract class GenericListPage extends ListRecords
{
    protected static string $component = 'inlay/resource/index';
}
