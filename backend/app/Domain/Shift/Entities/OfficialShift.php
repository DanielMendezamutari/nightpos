<?php

declare(strict_types=1);

namespace App\Domain\Shift\Entities;

final readonly class OfficialShift
{
    public function __construct(
        public int $id,
        public int $tenantId,
        public int $branchId,
        public string $name,
        public string $shiftType,
        public string $businessDate,
        public string $startsAt,
        public string $endsAt,
        public string $status,
        public int $openedByUserId,
        public ?int $closedByUserId,
        public string $openedAt,
        public ?string $closedAt,
        public ?string $notes,
        public ?string $openedByName = null,
        public ?string $closedByName = null,
        public ?string $branchName = null,
    ) {
    }
}
