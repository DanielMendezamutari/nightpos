<?php

declare(strict_types=1);

namespace App\Application\Branch\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class CreateBranchInput extends DataTransferObject
{
    public function __construct(
        public string $name,
        public string $code,
        public ?string $address,
        public string $status,
    ) {
    }
}
