<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class ListCashSessionsAdminInput
{
    public function __construct(
        public ?int $tenantId = null,
        public ?int $branchId = null,
        public ?int $officialShiftId = null,
        public ?int $cashierUserId = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
    ) {
    }
}
