<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericListPage;

final class BrandList extends GenericListPage
{
    protected static string $resource = BrandResource::class;

    protected int $perPage = 10;
}
