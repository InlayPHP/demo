<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericEditPage;

final class CustomerEdit extends GenericEditPage
{
    protected static string $resource = CustomerResource::class;
}
