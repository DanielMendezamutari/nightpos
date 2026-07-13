<?php

declare(strict_types=1);

namespace App\Domain\Cash\Contracts;

use App\Domain\Cash\ValueObjects\CashSessionId;

interface ScopeSummaryRepositoryInterface
{
    /**
     * @return array{
     *   tenant_id: int,
     *   branch_id: int,
     *   cash_session_id: int,
     *   cash_session_status: string,
     *   cash_session_opened_at: ?string,
     *   cash_session_closed_at: ?string,
     *   session_official_shift_id: ?int,
     *   current_official_shift_id: ?int,
     *   official_shift_ids_included: list<int>,
     *   open_shift_ids: list<int>,
     *   open_cash_sessions_on_historical_shifts: list<array{cash_session_id: int, official_shift_id: ?int, cash_session_status: string, official_shift_status: ?string, cash_session_opened_at: ?string}>
     * }
     */
    public function getScopeContext(CashSessionId $cashSessionId): array;
}
