<?php

declare(strict_types=1);

namespace App\Domain\User\Entities;

final class User
{
    public function __construct(
        private string $id,
        private string $tenantId,
        private ?string $branchId,
        private string $name,
        private string $username,
        private ?string $email,
        private string $role,
        private bool $isActive
    ) {}

    public function id(): string { return $this->id; }
    public function tenantId(): string { return $this->tenantId; }
    public function branchId(): ?string { return $this->branchId; }
    public function name(): string { return $this->name; }
    public function username(): string { return $this->username; }
    public function email(): ?string { return $this->email; }
    public function role(): string { return $this->role; }
    public function isActive(): bool { return $this->isActive; }

    public function canAccessPos(): bool
    {
        return in_array($this->role, ['cajero', 'mesero', 'barman', 'cocina', 'admin'], true);
    }
}
