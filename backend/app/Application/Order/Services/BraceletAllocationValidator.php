<?php

declare(strict_types=1);

namespace App\Application\Order\Services;

use App\Application\GirlIncome\Services\GirlStaffValidator;
use App\Domain\Order\Exceptions\OrderDomainException;
use App\Domain\Product\Entities\Product;
use App\Domain\Product\ValueObjects\AllocationType;

final class BraceletAllocationValidator
{
    public function __construct(
        private readonly GirlStaffValidator $girlStaffValidator,
    ) {
    }

    /**
     * @param  list<array{girl_user_id: int, units: int}>  $draftRows
     * @return list<array{girl_user_id: int, units: int, unit_amount: string, total_amount: string}>
     */
    public function validateAndBuildRows(
        int $tenantId,
        Product $product,
        int $quantity,
        ?string $girlAmountPerCombo,
        array $draftRows,
    ): array {
        if (! $product->requiresAllocation) {
            throw OrderDomainException::allocationNotAllowed();
        }

        $required = $product->requiredBraceletUnits($quantity);

        if ($girlAmountPerCombo === null || (float) $girlAmountPerCombo <= 0) {
            throw OrderDomainException::allocationsIncomplete($required, 0);
        }

        $merged = [];

        foreach ($draftRows as $row) {
            $girlUserId = (int) ($row['girl_user_id'] ?? 0);
            $units = (int) ($row['units'] ?? 0);

            if ($girlUserId <= 0 || $units <= 0) {
                continue;
            }

            $this->girlStaffValidator->assertGirl($tenantId, $girlUserId);
            $merged[$girlUserId] = ($merged[$girlUserId] ?? 0) + $units;
        }

        if ($merged === []) {
            throw OrderDomainException::allocationsIncomplete($required, 0);
        }

        $assigned = array_sum($merged);

        if ($assigned !== $required) {
            throw OrderDomainException::allocationsIncomplete($required, $assigned);
        }

        $unitAmount = round((float) $girlAmountPerCombo / $product->braceletUnitsPerLine, 2);
        $allocationType = $product->allocationType ?? AllocationType::GIRL_BRACELET_UNITS;
        $built = [];

        foreach ($merged as $girlUserId => $units) {
            $totalAmount = round($unitAmount * $units, 2);
            $built[] = [
                'girl_user_id' => $girlUserId,
                'units' => $units,
                'unit_amount' => number_format($unitAmount, 2, '.', ''),
                'total_amount' => number_format($totalAmount, 2, '.', ''),
            ];
        }

        return $built;
    }

    /**
     * @param  list<\App\Domain\Order\Entities\OrderItemAllocation>  $allocations
     */
    public function isComplete(Product $product, int $quantity, array $allocations): bool
    {
        if (! $product->requiresAllocation) {
            return true;
        }

        $required = $product->requiredBraceletUnits($quantity);
        $assigned = array_sum(array_map(static fn ($row) => $row->units, $allocations));

        return $assigned === $required;
    }

    /**
     * @param  list<\App\Domain\Order\Entities\OrderItemAllocation>  $allocations
     */
    public function assertComplete(Product $product, int $quantity, array $allocations): void
    {
        if (! $product->requiresAllocation) {
            return;
        }

        $required = $product->requiredBraceletUnits($quantity);
        $assigned = array_sum(array_map(static fn ($row) => $row->units, $allocations));

        if ($assigned !== $required) {
            throw OrderDomainException::allocationsIncomplete($required, $assigned);
        }
    }
}
