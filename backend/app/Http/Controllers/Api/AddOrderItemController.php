<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Modules\Sales\Application\DTO\AddOrderItemInput;
use App\Modules\Sales\Application\UseCases\AddOrderItemUseCase;
use App\Modules\Sales\Domain\Enums\ConsumptionType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class AddOrderItemController extends Controller
{
    public function __invoke(Request $request, AddOrderItemUseCase $useCase): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'waiter_id' => ['required', 'integer', 'exists:users,id'],
            'companion_id' => ['nullable', 'integer', 'exists:companions,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'consumption_type' => ['required', 'in:solo,with_companion'],
        ]);

        $consumptionType = ConsumptionType::from($payload['consumption_type']);

        if ($consumptionType === ConsumptionType::WITH_COMPANION && empty($payload['companion_id'])) {
            return response()->json([
                'message' => 'Companion is required for with_companion consumption type.',
            ], 422);
        }

        $useCase->execute(new AddOrderItemInput(
            orderId: $payload['order_id'],
            productId: $payload['product_id'],
            waiterId: $payload['waiter_id'],
            companionId: $payload['companion_id'] ?? null,
            quantity: $payload['quantity'],
            consumptionType: $consumptionType,
        ));

        return response()->json([
            'data' => [
                'order_id' => $payload['order_id'],
                'consumption_type' => $payload['consumption_type'],
            ],
        ], 201);
    }
}
