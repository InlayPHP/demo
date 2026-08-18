<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Pages;

use Inlay\Resources\Pages\EditRecord;

abstract class GenericEditPage extends EditRecord
{
    protected static string $component = 'inlay/resource/form';
}
