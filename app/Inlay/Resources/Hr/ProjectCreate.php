<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericCreatePage;

final class ProjectCreate extends GenericCreatePage
{
    protected static string $resource = ProjectResource::class;
}
