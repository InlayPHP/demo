<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericViewPage;

final class ProjectView extends GenericViewPage
{
    protected static string $resource = ProjectResource::class;
}
