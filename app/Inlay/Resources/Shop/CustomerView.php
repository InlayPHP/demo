<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericViewPage;

final class CustomerView extends GenericViewPage
{
    protected static string $resource = CustomerResource::class;
}
