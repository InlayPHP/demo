<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericEditPage;

final class ProductEdit extends GenericEditPage
{
    protected static string $resource = ProductResource::class;
}
