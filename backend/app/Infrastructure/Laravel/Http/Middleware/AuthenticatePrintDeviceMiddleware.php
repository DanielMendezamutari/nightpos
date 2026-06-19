<?php

declare(strict_types=1);

namespace App\Infrastructure\Laravel\Http\Middleware;

use App\Application\Printing\Services\PrintDeviceKeyService;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Infrastructure\Laravel\Http\Context\RequestPrintDeviceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class AuthenticatePrintDeviceMiddleware
{
    public function __construct(
        private readonly RequestPrintDeviceContext $context,
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintDeviceKeyService $keyService,
    ) {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $this->context->reset();

        $token = $request->bearerToken();

        if ($token === null || $token === '' || ! str_starts_with($token, 'npd_live_')) {
            throw PrintingDomainException::invalidDeviceKey();
        }

        $prefix = substr($token, 0, 12);
        $device = $this->devices->findByKeyPrefix($prefix);

        if ($device === null) {
            throw PrintingDomainException::invalidDeviceKey();
        }

        if (! $this->keyService->verify($token, (string) $device['device_key_hash'])) {
            throw PrintingDomainException::invalidDeviceKey();
        }

        if (! ($device['enabled'] ?? false) || ($device['status'] ?? '') !== 'ACTIVE') {
            throw PrintingDomainException::deviceDisabled();
        }

        unset($device['device_key_hash']);
        $this->context->setDevice($device);

        return $next($request);
    }
}
