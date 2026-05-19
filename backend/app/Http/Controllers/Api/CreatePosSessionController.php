<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Concerns\ResolvesBranchSiteId;
use App\Http\Controllers\Controller;
use App\Modules\Sales\Application\DTO\CreatePosSessionInput;
use App\Modules\Sales\Application\Exceptions\PosFlowException;
use App\Modules\Sales\Application\UseCases\CreatePosSessionUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class CreatePosSessionController extends Controller
{
    use ResolvesBranchSiteId;

    public function __invoke(Request $request, CreatePosSessionUseCase $useCase): JsonResponse
    {
        $siteId = $this->resolveBranchSiteId($request);
        if (! $siteId) {
            return response()->json(['message' => 'No se pudo resolver sucursal.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $payload = $request->validate([
            'site_table_id' => ['nullable', 'integer', 'exists:site_tables,id', 'required_without:table_code'],
            'table_code' => ['nullable', 'string', 'max:50'],
            'zone_code' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:120'],
        ]);

        try {
            $data = $useCase->execute(new CreatePosSessionInput(
                siteId: (int) $siteId,
                waiterUserId: (int) $request->user()->id,
                siteTableId: isset($payload['site_table_id']) ? (int) $payload['site_table_id'] : null,
                tableCode: $payload['table_code'] ?? null,
                zoneCode: $payload['zone_code'] ?? null,
                customerName: $payload['customer_name'] ?? null,
            ));
        } catch (PosFlowException $e) {
            return response()->json(['message' => $e->getMessage()], $e->statusCode);
        }

        return response()->json(['data' => $data], 201);
    }
}
