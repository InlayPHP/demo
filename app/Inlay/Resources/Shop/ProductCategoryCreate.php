<?php

declare(strict_types=1);

namespace App\Inlay\Resources\Shop;

use App\Inlay\Resources\Pages\GenericCreatePage;

final class ProductCategoryCreate extends GenericCreatePage
{
    protected static string $resource = ProductCategoryResource::class;
}
