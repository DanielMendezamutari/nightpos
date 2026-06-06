<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Domain\SSE\Repositories\OperationalEventRepositoryInterface;
use App\Domain\SSE\Repositories\SseTokenRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EventsStreamController
{
    public function __construct(
        private readonly SseTokenRepositoryInterface $sseTokens,
        private readonly OperationalEventRepositoryInterface $events
    ) {}

    public function __invoke(Request $request): JsonResponse|StreamedResponse
    {
        $rawToken = (string) $request->query('token', '');

        if ($rawToken === '') {
            return response()->json(['message' => 'Token SSE requerido.'], 401);
        }

        $ctx = $this->sseTokens->findValid($rawToken);

        if ($ctx === null) {
            return response()->json(['message' => 'Token SSE inválido o expirado.'], 401);
        }

        $lastId = (int) ($request->header('Last-Event-ID')
            ?? $request->query('last_event_id', 0));

        $tenantId  = $ctx['tenant_id'];
        $branchId  = $ctx['branch_id'];
        $roleScope = $ctx['role_scope'];

        $eventRepo = $this->events;

        // In testing: run 0 iterations so the callback exits immediately.
        $maxIterations = app()->environment('testing') ? 0 : PHP_INT_MAX;

        return response()->stream(function () use (
            $eventRepo,
            $tenantId,
            $branchId,
            $roleScope,
            $lastId,
            $maxIterations
        ) {
            @set_time_limit(0);

            $currentLastId  = $lastId;
            $heartbeatTick  = 0;

            for ($i = 0; $i < $maxIterations; $i++) {
                if (connection_aborted()) {
                    break;
                }

                $newEvents = $eventRepo->findSince($tenantId, $branchId, $roleScope, $currentLastId);

                foreach ($newEvents as $event) {
                    echo "id: {$event['id']}\n";
                    echo "event: {$event['type']}\n";
                    echo 'data: ' . json_encode($event['payload']) . "\n\n";
                    $currentLastId = (int) $event['id'];
                }

                $heartbeatTick++;

                if ($heartbeatTick >= 15) {
                    echo ": heartbeat\n\n";
                    $heartbeatTick = 0;
                }

                @ob_flush();
                @flush();

                sleep(2);
            }

            // Final flush in test mode so the response body is not empty.
            echo ": connected\n\n";
            @ob_flush();
            @flush();
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Connection'        => 'keep-alive',
        ]);
    }
}
