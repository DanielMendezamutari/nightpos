<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Context;

final class RequestPrintDeviceContext
{
    /** @var array<string, mixed>|null */
    private ?array $device = null;

    public function reset(): void
    {
        $this->device = null;
    }

    /**
     * @param  array<string, mixed>  $device
     */
    public function setDevice(array $device): void
    {
        $this->device = $device;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function device(): ?array
    {
        return $this->device;
    }

    public function deviceId(): ?int
    {
        return $this->device !== null ? (int) $this->device['id'] : null;
    }

    public function tenantId(): ?int
    {
        return $this->device !== null ? (int) $this->device['tenant_id'] : null;
    }

    public function branchId(): ?int
    {
        return $this->device !== null ? (int) $this->device['branch_id'] : null;
    }
}
