<?php

declare(strict_types=1);

namespace App\Application\Cash\Services\SummaryBuilders;

use App\Application\Cash\DTOs\ScopeSummaryDTO;
use App\Domain\Cash\Contracts\ScopeSummaryRepositoryInterface;
use App\Domain\Cash\Contracts\SummaryBuilders\ScopeSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;

final readonly class EloquentScopeSummaryBuilder implements ScopeSummaryBuilder
{
    public function __construct(
        private ScopeSummaryRepositoryInterface $scopeRepository,
    ) {
    }

    public function build(CashSessionId $cashSessionId): ScopeSummaryDTO
    {
        $context = $this->scopeRepository->getScopeContext($cashSessionId);

        $tenantId = (int) ($context['tenant_id'] ?? 0);
        $branchId = (int) ($context['branch_id'] ?? 0);
        $sessionId = (int) ($context['cash_session_id'] ?? $cashSessionId->value);
        $cashSessionStatus = strtoupper((string) ($context['cash_session_status'] ?? 'UNKNOWN'));
        $sessionShiftId = isset($context['session_official_shift_id']) ? (int) $context['session_official_shift_id'] : null;
        $currentShiftId = isset($context['current_official_shift_id']) ? (int) $context['current_official_shift_id'] : null;

        $includedShiftIds = $this->normalizeIntList($context['official_shift_ids_included'] ?? []);
        $openShiftIds = $this->normalizeIntList($context['open_shift_ids'] ?? []);
        $openCashSessionsOnHistoricalShifts = $this->normalizeHistoricalOpenCashSessions(
            $context['open_cash_sessions_on_historical_shifts'] ?? []
        );

        $crossesMultipleShifts = count($includedShiftIds) > 1;
        $hasOpenShiftConflict = count($openShiftIds) > 1;

        $scopeType = $this->resolveScopeType($includedShiftIds, $sessionShiftId, $currentShiftId);
        $scopeLabel = $this->buildScopeLabel($scopeType, $sessionId, $cashSessionStatus, $includedShiftIds, $sessionShiftId, $currentShiftId);
        $warnings = $this->buildWarnings(
            includedShiftIds: $includedShiftIds,
            crossesMultipleShifts: $crossesMultipleShifts,
            hasOpenShiftConflict: $hasOpenShiftConflict,
            openCashSessionsOnHistoricalShifts: $openCashSessionsOnHistoricalShifts,
            cashSessionStatus: $cashSessionStatus,
            sessionShiftId: $sessionShiftId,
            currentShiftId: $currentShiftId,
        );

        return new ScopeSummaryDTO(
            tenant_id: $tenantId,
            branch_id: $branchId,
            cash_session_id: $sessionId,
            cash_session_status: $cashSessionStatus,
            cash_session_opened_at: $context['cash_session_opened_at'] ?? null,
            cash_session_closed_at: $context['cash_session_closed_at'] ?? null,
            session_official_shift_id: $sessionShiftId,
            current_official_shift_id: $currentShiftId,
            official_shift_ids_included: $includedShiftIds,
            crosses_multiple_shifts: $crossesMultipleShifts,
            open_shift_ids: $openShiftIds,
            has_open_shift_conflict: $hasOpenShiftConflict,
            open_cash_sessions_on_historical_shifts: $openCashSessionsOnHistoricalShifts,
            scope_type: $scopeType,
            scope_label: $scopeLabel,
            warnings: $warnings,
        );
    }

    /**
     * @param  array<mixed>  $ids
     * @return list<int>
     */
    private function normalizeIntList(array $ids): array
    {
        $normalized = array_map(static fn ($id) => (int) $id, $ids);
        $normalized = array_values(array_unique($normalized));
        sort($normalized);

        return $normalized;
    }

    /**
     * @param  array<mixed>  $sessions
     * @return list<array{cash_session_id: int, official_shift_id: ?int, cash_session_status: string, official_shift_status: ?string, cash_session_opened_at: ?string}>
     */
    private function normalizeHistoricalOpenCashSessions(array $sessions): array
    {
        $normalized = [];

        foreach ($sessions as $session) {
            if (! is_array($session)) {
                continue;
            }

            $normalized[] = [
                'cash_session_id' => (int) ($session['cash_session_id'] ?? 0),
                'official_shift_id' => isset($session['official_shift_id']) ? (int) $session['official_shift_id'] : null,
                'cash_session_status' => strtoupper((string) ($session['cash_session_status'] ?? 'UNKNOWN')),
                'official_shift_status' => isset($session['official_shift_status'])
                    ? strtoupper((string) $session['official_shift_status'])
                    : null,
                'cash_session_opened_at' => $session['cash_session_opened_at'] ?? null,
            ];
        }

        usort(
            $normalized,
            static fn (array $left, array $right): int => $left['cash_session_id'] <=> $right['cash_session_id']
        );

        return $normalized;
    }

    /**
     * @param  list<int>  $includedShiftIds
     */
    private function resolveScopeType(array $includedShiftIds, ?int $sessionShiftId, ?int $currentShiftId): string
    {
        if (count($includedShiftIds) > 1) {
            return 'HYBRID';
        }

        if (
            count($includedShiftIds) === 1
            && $sessionShiftId !== null
            && $includedShiftIds[0] === $sessionShiftId
        ) {
            return 'OFFICIAL_SHIFT';
        }

        if (
            $sessionShiftId !== null
            && $currentShiftId !== null
            && $sessionShiftId === $currentShiftId
        ) {
            return 'OFFICIAL_SHIFT';
        }

        return 'CASH_SESSION';
    }

    /**
     * @param  list<int>  $includedShiftIds
     */
    private function buildScopeLabel(
        string $scopeType,
        int $sessionId,
        string $cashSessionStatus,
        array $includedShiftIds,
        ?int $sessionShiftId,
        ?int $currentShiftId,
    ): string {
        $sessionState = $cashSessionStatus === 'OPEN' ? 'abierta' : 'cerrada';

        if ($scopeType === 'HYBRID') {
            return sprintf(
                'Caja #%d (%s) · Multi-turno [%s]',
                $sessionId,
                $sessionState,
                implode(', ', $includedShiftIds),
            );
        }

        if ($scopeType === 'OFFICIAL_SHIFT') {
            $shiftId = $sessionShiftId ?? $currentShiftId ?? ($includedShiftIds[0] ?? null);

            if ($shiftId !== null) {
                return sprintf('Turno #%d · Caja #%d (%s)', $shiftId, $sessionId, $sessionState);
            }
        }

        return sprintf('Caja #%d (%s)', $sessionId, $sessionState);
    }

    /**
     * @param  list<int>  $includedShiftIds
     * @param  list<array{cash_session_id: int, official_shift_id: ?int, cash_session_status: string, official_shift_status: ?string, cash_session_opened_at: ?string}>  $openCashSessionsOnHistoricalShifts
     * @return list<string>
     */
    private function buildWarnings(
        array $includedShiftIds,
        bool $crossesMultipleShifts,
        bool $hasOpenShiftConflict,
        array $openCashSessionsOnHistoricalShifts,
        string $cashSessionStatus,
        ?int $sessionShiftId,
        ?int $currentShiftId,
    ): array {
        $warnings = [];

        if ($crossesMultipleShifts) {
            $warnings[] = 'La caja incluye actividad de multiples official_shift_id.';
        }

        if ($includedShiftIds === []) {
            $warnings[] = 'No se detectaron official_shift_id en sales/cash_movements/staff_settlements para esta caja.';
        }

        if ($hasOpenShiftConflict) {
            $warnings[] = 'Existen multiples turnos OPEN en la sucursal.';
        }

        if ($openCashSessionsOnHistoricalShifts !== []) {
            $warnings[] = 'Se detectaron cajas OPEN vinculadas a turnos historicos/cerrados.';
        }

        if (
            $cashSessionStatus === 'OPEN'
            && $sessionShiftId !== null
            && $currentShiftId !== null
            && $sessionShiftId !== $currentShiftId
        ) {
            $warnings[] = 'El turno oficial de la caja no coincide con el turno OPEN actual.';
        }

        return $warnings;
    }
}
