<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Hr;

use App\Inlay\Resources\Pages\GenericEditPage;

final class ExpenseEdit extends GenericEditPage
{
    protected static string $resource = ExpenseResource::class;
}
