<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Cash\Contracts\ScopeSummaryRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\CashSessionModel;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\SaleModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

final class EloquentScopeSummaryRepository implements ScopeSummaryRepositoryInterface
{
    public function getScopeContext(CashSessionId $cashSessionId): array
    {
        $session = CashSessionModel::query()
            ->where('id', $cashSessionId->value)
            ->first([
                'id',
                'tenant_id',
                'branch_id',
                'status',
                'official_shift_id',
                'opened_at',
                'closed_at',
            ]);

        if ($session === null) {
            throw new InvalidArgumentException(sprintf('cash_session_id %d not found.', $cashSessionId->value));
        }

        $tenantId = (int) $session->getAttribute('tenant_id');
        $branchId = (int) $session->getAttribute('branch_id');

        $salesShiftIds = SaleModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', $cashSessionId->value)
            ->whereNotNull('official_shift_id')
            ->distinct()
            ->pluck('official_shift_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $settlementShiftIds = StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('cash_session_id', $cashSessionId->value)
            ->whereNotNull('official_shift_id')
            ->distinct()
            ->pluck('official_shift_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $movementSaleShiftIds = CashMovementModel::query()
            ->join('sales as s', function ($join): void {
                $join->on('s.id', '=', 'cash_movements.source_id')
                    ->where('cash_movements.source_type', '=', 'SALE');
            })
            ->where('cash_movements.tenant_id', $tenantId)
            ->where('cash_movements.branch_id', $branchId)
            ->where('cash_movements.cash_session_id', $cashSessionId->value)
            ->whereNotNull('s.official_shift_id')
            ->distinct()
            ->pluck('s.official_shift_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $movementSettlementShiftIds = CashMovementModel::query()
            ->join('staff_settlements as ss', function ($join): void {
                $join->on('ss.id', '=', 'cash_movements.source_id')
                    ->where('cash_movements.source_type', '=', 'STAFF_SETTLEMENT');
            })
            ->where('cash_movements.tenant_id', $tenantId)
            ->where('cash_movements.branch_id', $branchId)
            ->where('cash_movements.cash_session_id', $cashSessionId->value)
            ->whereNotNull('ss.official_shift_id')
            ->distinct()
            ->pluck('ss.official_shift_id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $officialShiftIdsIncluded = array_values(array_unique(array_merge(
            $salesShiftIds,
            $settlementShiftIds,
            $movementSaleShiftIds,
            $movementSettlementShiftIds,
        )));
        sort($officialShiftIdsIncluded);

        $openShiftIds = OfficialShiftModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->orderBy('id')
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->all();

        $currentOfficialShiftId = OfficialShiftModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('status', 'OPEN')
            ->orderByDesc('opened_at')
            ->orderByDesc('id')
            ->value('id');

        $openCashSessionsOnHistoricalShifts = CashSessionModel::query()
            ->leftJoin('official_shifts as os', 'os.id', '=', 'cash_sessions.official_shift_id')
            ->where('cash_sessions.tenant_id', $tenantId)
            ->where('cash_sessions.branch_id', $branchId)
            ->where('cash_sessions.status', 'OPEN')
            ->where(function ($query): void {
                $query->whereNull('cash_sessions.official_shift_id')
                    ->orWhereNull('os.id')
                    ->orWhere('os.status', '!=', 'OPEN')
                    ->orWhereNotNull('os.closed_at');
            })
            ->orderBy('cash_sessions.id')
            ->get([
                'cash_sessions.id as cash_session_id',
                'cash_sessions.official_shift_id',
                'cash_sessions.status as cash_session_status',
                'cash_sessions.opened_at as cash_session_opened_at',
                'os.status as official_shift_status',
            ])
            ->map(static function ($row): array {
                $openedAt = $row->getAttribute('cash_session_opened_at');

                return [
                    'cash_session_id' => (int) $row->getAttribute('cash_session_id'),
                    'official_shift_id' => $row->getAttribute('official_shift_id') !== null
                        ? (int) $row->getAttribute('official_shift_id')
                        : null,
                    'cash_session_status' => strtoupper((string) $row->getAttribute('cash_session_status')),
                    'official_shift_status' => $row->getAttribute('official_shift_status') !== null
                        ? strtoupper((string) $row->getAttribute('official_shift_status'))
                        : null,
                    'cash_session_opened_at' => $openedAt !== null ? Carbon::parse((string) $openedAt)->toIso8601String() : null,
                ];
            })
            ->toArray();

        $sessionOpenedAt = $session->getAttribute('opened_at');
        $sessionClosedAt = $session->getAttribute('closed_at');

        return [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'cash_session_id' => $cashSessionId->value,
            'cash_session_status' => strtoupper((string) $session->getAttribute('status')),
            'session_official_shift_id' => $session?->getAttribute('official_shift_id') !== null
                ? (int) $session->getAttribute('official_shift_id')
                : null,
            'cash_session_opened_at' => $sessionOpenedAt?->toIso8601String(),
            'cash_session_closed_at' => $sessionClosedAt?->toIso8601String(),
            'current_official_shift_id' => $currentOfficialShiftId !== null ? (int) $currentOfficialShiftId : null,
            'official_shift_ids_included' => $officialShiftIdsIncluded,
            'open_shift_ids' => $openShiftIds,
            'open_cash_sessions_on_historical_shifts' => $openCashSessionsOnHistoricalShifts,
        ];
    }
}
