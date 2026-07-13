<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class ScopeSummaryDTO
{
    public function __construct(
        public int $tenant_id,
        public int $branch_id,
        public int $cash_session_id,
        public string $cash_session_status,
        public ?string $cash_session_opened_at,
        public ?string $cash_session_closed_at,
        public ?int $session_official_shift_id,
        public ?int $current_official_shift_id,
        /** @var list<int> */
        public array $official_shift_ids_included,
        public bool $crosses_multiple_shifts,
        /** @var list<int> */
        public array $open_shift_ids,
        public bool $has_open_shift_conflict,
        /** @var list<array{cash_session_id: int, official_shift_id: ?int, cash_session_status: string, official_shift_status: ?string, cash_session_opened_at: ?string}> */
        public array $open_cash_sessions_on_historical_shifts,
        public string $scope_type,
        public string $scope_label,
        /** @var list<string> */
        public array $warnings,
    ) {
    }
}
