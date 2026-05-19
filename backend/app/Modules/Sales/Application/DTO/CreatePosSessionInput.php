<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\DTO;

final readonly class CreatePosSessionInput
{
    public function __construct(
        public int $siteId,
        public int $waiterUserId,
        public ?int $siteTableId,
        public ?string $tableCode,
        public ?string $zoneCode,
        public ?string $customerName,
    ) {
    }
}
