<?php

declare(strict_types=1);

namespace App\Inlay\Resources;

use Inlay\Resources\Pages\ListRecords;

final class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected static string $component = 'users/index';

    protected int $perPage = 10;
}
