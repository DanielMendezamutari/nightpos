<?php

declare(strict_types=1);

use App\Shared\Domain\ValueObjects\TenantId;

it('creates a valid tenant id', function () {
    $id = new TenantId('tenant-1');

    expect((string) $id)->toBe('tenant-1');
});

it('rejects empty tenant id', function () {
    new TenantId('');
})->throws(InvalidArgumentException::class);
