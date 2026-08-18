<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Pages;

use Inlay\Resources\Pages\CreateRecord;

abstract class GenericCreatePage extends CreateRecord
{
    protected static string $component = 'inlay/resource/form';
}
