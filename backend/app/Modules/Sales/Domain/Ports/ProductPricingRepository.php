<?php

declare(strict_types=1);

namespace App\Modules\Sales\Domain\Ports;

interface ProductPricingRepository
{
    /**
     * @return array{price_solo:int,price_with_companion:int}
     */
    public function getPricingByProductId(int $productId): array;
}
