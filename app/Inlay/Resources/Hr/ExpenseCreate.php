<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericCreatePage;

final class ExpenseCreate extends GenericCreatePage
{
    protected static string $resource = ExpenseResource::class;
}
