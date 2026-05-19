<?php

declare(strict_types=1);

namespace App\Modules\Sales\Domain\Services;

use App\Modules\Sales\Domain\Enums\ConsumptionType;

final class PricingPolicy
{
    public function resolveDrinkPrice(
        ConsumptionType $consumptionType,
        int $priceSolo,
        int $priceWithCompanion
    ): int
    {
        return match ($consumptionType) {
            ConsumptionType::SOLO => $priceSolo,
            ConsumptionType::WITH_COMPANION => $priceWithCompanion,
        };
    }
}
