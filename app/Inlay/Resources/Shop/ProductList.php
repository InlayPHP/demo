<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericListPage;

final class ProductList extends GenericListPage
{
    protected static string $resource = ProductResource::class;

    protected int $perPage = 10;
}
