<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Order\DTOs\AddOrderItemInput;
use App\Application\Order\DTOs\AssignOrderItemGirlInput;
use App\Application\Order\DTOs\CreateOrderInput;
use App\Application\Order\DTOs\GetOrderInput;
use App\Application\Order\DTOs\ListOrdersInput;
use App\Application\Order\DTOs\CancelOrderItemInput;
use App\Application\Order\DTOs\OrderActionInput;
use App\Application\Order\DTOs\RemoveOrderItemInput;
use App\Application\Order\DTOs\UpdateOrderHeaderInput;
use App\Application\Order\DTOs\UpdateOrderItemInput;
use App\Application\Order\UseCases\AddOrderItemUseCase;
use App\Application\Order\UseCases\AssignOrderItemGirlUseCase;
use App\Application\Order\UseCases\CancelOrderItemUseCase;
use App\Application\Order\UseCases\CancelOrderUseCase;
use App\Application\Order\UseCases\CreateOrderUseCase;
use App\Application\Order\UseCases\GetOrderUseCase;
use App\Application\Order\UseCases\ListOrdersUseCase;
use App\Application\Order\UseCases\RemoveOrderItemUseCase;
use App\Application\Order\UseCases\SendOrderToBarUseCase;
use App\Application\Order\UseCases\UpdateOrderHeaderUseCase;
use App\Application\Order\UseCases\UpdateOrderItemUseCase;
use App\Application\Sale\DTOs\ChargeOrderInput;
use App\Application\Sale\UseCases\ChargeOrderUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Order\AddOrderItemRequest;
use App\Http\Requests\Api\V1\Order\AssignOrderItemGirlRequest;
use App\Http\Requests\Api\V1\Order\CancelOrderItemRequest;
use App\Http\Requests\Api\V1\Order\CreateOrderRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderHeaderRequest;
use App\Http\Requests\Api\V1\Order\UpdateOrderItemRequest;
use App\Http\Requests\Api\V1\Sale\ChargeOrderRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class OrderController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListOrdersUseCase $listOrders,
        private readonly CreateOrderUseCase $createOrder,
        private readonly GetOrderUseCase $getOrder,
        private readonly AddOrderItemUseCase $addOrderItem,
        private readonly AssignOrderItemGirlUseCase $assignOrderItemGirl,
        private readonly SendOrderToBarUseCase $sendOrderToBar,
        private readonly CancelOrderUseCase $cancelOrder,
        private readonly UpdateOrderItemUseCase $updateOrderItem,
        private readonly RemoveOrderItemUseCase $removeOrderItem,
        private readonly CancelOrderItemUseCase $cancelOrderItem,
        private readonly UpdateOrderHeaderUseCase $updateOrderHeader,
        private readonly ChargeOrderUseCase $chargeOrder,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $status = $request->query('status');
        $scope = $request->query('scope');
        $currentShiftOnly = filter_var($request->query('current_shift', false), FILTER_VALIDATE_BOOLEAN);

        return $this->presenter->present($this->listOrders->execute(
            new ListOrdersInput(
                is_string($status) && $status !== '' ? strtoupper($status) : null,
                $currentShiftOnly,
                is_string($scope) && $scope !== '' ? $scope : null,
            )
        ));
    }

    public function store(CreateOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->createOrder->execute(new CreateOrderInput(
            tableLabel: $validated['table_label'] ?? null,
            serviceAreaId: isset($validated['service_area_id']) ? (int) $validated['service_area_id'] : null,
            waiterUserId: isset($validated['waiter_user_id']) ? (int) $validated['waiter_user_id'] : null,
            notes: $validated['notes'] ?? null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getOrder->execute(new GetOrderInput($id)));
    }

    public function addItem(int $id, AddOrderItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->addOrderItem->execute(new AddOrderItemInput(
            orderId: $id,
            productId: (int) $validated['product_id'],
            saleMode: $validated['sale_mode'],
            quantity: (int) ($validated['quantity'] ?? 1),
            girlUserId: isset($validated['girl_user_id']) ? (int) $validated['girl_user_id'] : null,
            notes: $validated['notes'] ?? null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function assignItemGirl(int $id, int $itemId, AssignOrderItemGirlRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->assignOrderItemGirl->execute(new AssignOrderItemGirlInput(
            orderId: $id,
            itemId: $itemId,
            girlUserId: (int) $validated['girl_user_id'],
        ));

        return $this->presenter->present($result);
    }

    public function sendToBar(int $id): JsonResponse
    {
        return $this->presenter->present($this->sendOrderToBar->execute(new OrderActionInput($id)));
    }

    public function updateItem(int $id, int $itemId, UpdateOrderItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updateOrderItem->execute(new UpdateOrderItemInput(
            orderId: $id,
            itemId: $itemId,
            productId: isset($validated['product_id']) ? (int) $validated['product_id'] : null,
            quantity: isset($validated['quantity']) ? (int) $validated['quantity'] : null,
            saleMode: $validated['sale_mode'] ?? null,
            girlUserId: array_key_exists('girl_user_id', $validated) && $validated['girl_user_id'] !== null
                ? (int) $validated['girl_user_id']
                : null,
            clearGirl: array_key_exists('girl_user_id', $validated) && $validated['girl_user_id'] === null,
            reason: $validated['reason'] ?? null,
        ));

        return $this->presenter->present($result);
    }

    public function removeItem(int $id, int $itemId): JsonResponse
    {
        return $this->presenter->present($this->removeOrderItem->execute(
            new RemoveOrderItemInput($id, $itemId),
        ));
    }

    public function cancelItem(int $id, int $itemId, CancelOrderItemRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->cancelOrderItem->execute(
            new CancelOrderItemInput($id, $itemId, $validated['reason']),
        ));
    }

    public function updateHeader(int $id, UpdateOrderHeaderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->updateOrderHeader->execute(new UpdateOrderHeaderInput(
            orderId: $id,
            tableLabel: array_key_exists('table_label', $validated) ? $validated['table_label'] : null,
            serviceAreaId: array_key_exists('service_area_id', $validated) && $validated['service_area_id'] !== null
                ? (int) $validated['service_area_id']
                : null,
            notes: array_key_exists('notes', $validated) ? $validated['notes'] : null,
            clearServiceArea: array_key_exists('service_area_id', $validated) && $validated['service_area_id'] === null,
        ));

        return $this->presenter->present($result);
    }

    public function cancel(int $id): JsonResponse
    {
        return $this->presenter->present($this->cancelOrder->execute(new OrderActionInput($id)));
    }

    public function charge(int $id, ChargeOrderRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $payments = array_map(static fn (array $row) => [
            'method' => $row['method'],
            'amount' => $row['amount'],
        ], $validated['payments']);

        $result = $this->chargeOrder->execute(new ChargeOrderInput(
            orderId: $id,
            payments: $payments,
        ));

        return $this->presenter->present($result, 201);
    }
}
