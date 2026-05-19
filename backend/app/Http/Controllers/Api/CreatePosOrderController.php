<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Modules\Sales\Application\DTO\CreatePosOrderInput;
use App\Modules\Sales\Application\Exceptions\PosFlowException;
use App\Modules\Sales\Application\UseCases\CreatePosOrderUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreatePosOrderController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request, CreatePosOrderUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'customer_session_id' => ['required', 'integer', 'exists:customer_sessions,id'],
        ]);

        $siteId = $this->resolveBranchSiteId($request);

        try {
            $data = $useCase->execute(new CreatePosOrderInput(
                customerSessionId: (int) $payload['customer_session_id'],
                waiterUserId: (int) $request->user()->id,
                resolvedSiteId: $siteId,
            ));
        } catch (PosFlowException $e) {
            return response()->json(['message' => $e->getMessage()], $e->statusCode);
        }

        return response()->json(['data' => $data], 201);
    }
}
