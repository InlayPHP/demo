<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericViewPage;

final class ProductView extends GenericViewPage
{
    protected static string $resource = ProductResource::class;
}
