<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

final readonly class CloseCashSessionInput extends CashDto
{
    public function __construct(
        public string $declaredClosingAmount,
        public ?string $closingNotes = null,
    ) {
    }
}
