<?php

declare(strict_types=1);

namespace App\Domain\Product\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class ProductCategoryNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Categoría no encontrada.');
    }
}
