<?php

declare(strict_types=1);

namespace App\Domain\Product\Services;

use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\ValueObjects\SaleMode;

final class ProductPriceValidator
{
    public function validate(
        string $saleMode,
        string $price,
        ?string $girlAmount,
        ?string $houseAmount,
    ): void {
        $mode = SaleMode::fromString($saleMode);

        if ((float) $price < 0) {
            throw ProductDomainException::negativePrice();
        }

        if ($girlAmount !== null && (float) $girlAmount < 0) {
            throw ProductDomainException::negativePrice();
        }

        if ($houseAmount !== null && (float) $houseAmount < 0) {
            throw ProductDomainException::negativePrice();
        }

        if (! $mode->isConAcompanante()) {
            if ($girlAmount !== null || $houseAmount !== null) {
                throw ProductDomainException::splitOnlyForConAcompanante();
            }

            return;
        }

        if ($girlAmount !== null && $houseAmount !== null) {
            $sum = round((float) $girlAmount + (float) $houseAmount, 2);
            $total = round((float) $price, 2);

            if (abs($sum - $total) > 0.001) {
                throw ProductDomainException::splitMustEqualPrice();
            }
        }
    }
}
