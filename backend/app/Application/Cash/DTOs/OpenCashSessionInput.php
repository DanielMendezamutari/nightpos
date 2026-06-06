<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class OpenCashSessionInput extends CashDto
{
    public function __construct(
        public string $openingAmount,
        public ?int $cashRegisterId = null,
        public ?string $openingNotes = null,
    ) {
    }
}
