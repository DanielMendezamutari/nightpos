<?php

declare(strict_types=1);

namespace App\Application\Product\Support;

use App\Domain\Product\Exceptions\ProductDomainException;
use App\Domain\Product\ValueObjects\AllocationType;
use App\Domain\Product\ValueObjects\SettlementBehavior;

final class ProductSettlementNormalizer
{
    /**
     * @return array{
     *   settlement_behavior: string,
     *   bracelet_units_per_line: int,
     *   requires_allocation: bool,
     *   allocation_type: string|null,
     * }
     */
    public function normalize(
        string $settlementBehavior,
        int $braceletUnitsPerLine,
        ?bool $requiresAllocation = null,
        ?string $allocationType = null,
    ): array {
        $behavior = SettlementBehavior::fromString($settlementBehavior);

        if ($braceletUnitsPerLine < 1) {
            throw ProductDomainException::invalidBraceletUnits();
        }

        if ($behavior->requiresAllocation()) {
            return [
                'settlement_behavior' => SettlementBehavior::GIRL_BRACELET_ALLOCATION,
                'bracelet_units_per_line' => $braceletUnitsPerLine,
                'requires_allocation' => true,
                'allocation_type' => AllocationType::GIRL_BRACELET_UNITS,
            ];
        }

        return [
            'settlement_behavior' => $behavior->value,
            'bracelet_units_per_line' => $behavior->value === SettlementBehavior::GIRL_LINE ? 1 : $braceletUnitsPerLine,
            'requires_allocation' => false,
            'allocation_type' => null,
        ];
    }
}
