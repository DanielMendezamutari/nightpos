<?php

declare(strict_types=1);

namespace App\Application\User\DTOs;

use App\Shared\Application\DTOs\DataTransferObject;

final readonly class UpdateUserInput extends DataTransferObject
{
    public function __construct(
        public int $userId,
        public string $name,
        public string $username,
        public ?string $email,
        public ?int $branchId,
        public ?int $roleId,
        public string $status,
        public ?string $staffRole,
        public ?string $waiterCommissionPercent,
        public ?bool $canReceiveGirlCommissions = null,
        public ?string $cleaningBaseAmount = null,
        public ?string $cleaningRoomAmount = null,
        /** @var list<int> */
        public array $accessibleBranchIds = [],
    ) {
    }
}
