<?php

declare(strict_types=1);

it('loads domain repository ports', function () {
    expect(interface_exists(\App\Domain\Tenant\Repositories\TenantRepositoryInterface::class))->toBeTrue();
    expect(interface_exists(\App\Domain\Sale\Repositories\SaleRepositoryInterface::class))->toBeTrue();
});

it('loads application use case contracts', function () {
    expect(interface_exists(\App\Application\Order\Contracts\OrderUseCaseInterface::class))->toBeTrue();
});

it('loads shared contracts without laravel domain coupling', function () {
    $reflection = new ReflectionClass(\App\Shared\Domain\Exceptions\DomainException::class);

    expect($reflection->getNamespaceName())->toBe('App\Shared\Domain\Exceptions');
    expect($reflection->isSubclassOf(Exception::class))->toBeTrue();
});
