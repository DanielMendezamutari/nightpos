<?php

declare(strict_types=1);

namespace App\Application\Printing\Services;

use App\Application\Reports\Services\ShiftManagerialSummaryBuilder;
use App\Domain\Shift\Repositories\OfficialShiftRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\ShiftClosureModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;

final class ShiftClosePrintPayloadEnricher
{
    public function __construct(
        private readonly ShiftManagerialSummaryBuilder $managerial,
        private readonly OfficialShiftRepositoryInterface $shifts,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function enrich(array $payload, int $tenantId, int $branchId, int $shiftId): array
    {
        $shift = $this->shifts->findById($shiftId, $tenantId);
        $closure = $this->shifts->findClosureByShiftId($shiftId, $tenantId);

        $managerial = $this->managerial->forShift($tenantId, $branchId, $shiftId, $closure);

        $summary = $payload['summary'] ?? [];
        if ($closure !== null) {
            $summary = array_merge($summary, [
                'total_waiter_payouts' => $closure->totalWaiterPayouts,
                'total_girl_payouts' => $closure->totalGirlPayouts,
                'total_cleaning_payouts' => $this->cleaningPayouts($shiftId, $tenantId),
            ]);
        }

        $payload['summary'] = $summary;
        $payload['managerial'] = $managerial;

        if ($shift !== null) {
            $payload['duration_minutes'] = $this->durationMinutes($shift->openedAt, $shift->closedAt ?? '');
        }

        $tenant = TenantModel::query()->find($tenantId);
        if ($tenant !== null) {
            $payload['tenant_name'] = (string) ($tenant->name ?? '');
        }

        return $payload;
    }

    private function cleaningPayouts(int $shiftId, int $tenantId): ?string
    {
        $value = ShiftClosureModel::query()
            ->where('official_shift_id', $shiftId)
            ->where('tenant_id', $tenantId)
            ->value('total_cleaning_payouts');

        return $value !== null ? (string) $value : null;
    }

    private function durationMinutes(string $openedAt, string $closedAt): ?int
    {
        if ($openedAt === '' || $closedAt === '') {
            return null;
        }

        $start = strtotime($openedAt);
        $end = strtotime($closedAt);

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return (int) round(($end - $start) / 60);
    }
}
