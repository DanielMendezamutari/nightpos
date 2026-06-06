<?php

declare(strict_types=1);

namespace App\Domain\Product\Exceptions;

use App\Shared\Domain\Exceptions\DomainException;

final class ProductNotFoundException extends DomainException
{
    public function __construct()
    {
        parent::__construct('Producto no encontrado.');
    }
}
