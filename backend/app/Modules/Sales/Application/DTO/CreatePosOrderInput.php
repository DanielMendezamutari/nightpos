<?php

declare(strict_types=1);

namespace App\Modules\Sales\Application\DTO;

final readonly class CreatePosOrderInput
{
    public function __construct(
        public int $customerSessionId,
        public int $waiterUserId,
        public ?int $resolvedSiteId,
    ) {
    }
}
