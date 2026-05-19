<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Application\DTO\AddOrderItemInput;
use App\Modules\Sales\Application\UseCases\AddOrderItemUseCase;
use App\Modules\Sales\Domain\Enums\ConsumptionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AddPosOrderItemController extends Controller
{
    public function __invoke(int $orderId, Request $request, AddOrderItemUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'companion_id' => ['nullable', 'integer', 'exists:companions,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'consumption_type' => ['required', 'in:solo,with_companion'],
        ]);

        $consumptionType = ConsumptionType::from($payload['consumption_type']);
        if ($consumptionType === ConsumptionType::WITH_COMPANION && empty($payload['companion_id'])) {
            return response()->json(['message' => 'Companion es requerido para consumo con chica.'], 422);
        }

        $useCase->execute(new AddOrderItemInput(
            orderId: $orderId,
            productId: (int) $payload['product_id'],
            waiterId: (int) $request->user()->id,
            companionId: $payload['companion_id'] ?? null,
            quantity: (int) $payload['quantity'],
            consumptionType: $consumptionType,
        ));

        return response()->json(['data' => ['order_id' => $orderId]], 201);
    }
}

