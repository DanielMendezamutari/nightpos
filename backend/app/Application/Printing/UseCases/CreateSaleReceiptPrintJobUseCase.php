<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Order\Services\OrderPresentationService;
use App\Application\Printing\Services\BranchPrintSettingsReader;
use App\Application\Printing\Services\PrintTicketContentBuilder;
use App\Application\SSE\Services\OperationalEventEmitter;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Printing\Repositories\PrintDeviceRepositoryInterface;
use App\Domain\Printing\Repositories\PrintJobRepositoryInterface;
use App\Domain\Sale\Entities\Sale;
use App\Domain\Settings\Repositories\ServiceAreaRepositoryInterface;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Shared\Domain\Enums\PrintJobSourceType;
use App\Shared\Domain\Enums\PrintJobStatus;
use App\Shared\Domain\Enums\PrintJobType;

final class CreateSaleReceiptPrintJobUseCase
{
    public function __construct(
        private readonly BranchPrintSettingsReader $branchSettings,
        private readonly PrintDeviceRepositoryInterface $devices,
        private readonly PrintJobRepositoryInterface $jobs,
        private readonly OrderRepositoryInterface $orders,
        private readonly OrderPresentationService $presentation,
        private readonly PrintTicketContentBuilder $contentBuilder,
        private readonly UserRepositoryInterface $users,
        private readonly ServiceAreaRepositoryInterface $serviceAreas,
        private readonly OperationalEventEmitter $eventEmitter,
    ) {
    }

    /**
     * @return array{job: ?array<string, mixed>, warning: ?string}
     */
    public function execute(
        Sale $sale,
        int $tenantId,
        int $branchId,
        ?int $requestedByUserId,
        ?Order $order = null,
        ?string $idempotencyKey = null,
    ): array {
        if (! $this->branchSettings->isAutoPrintSaleReceiptEnabled($branchId)) {
            return ['job' => null, 'warning' => null];
        }

        if (! $this->devices->hasActiveDevice($tenantId, $branchId)) {
            return [
                'job' => null,
                'warning' => 'No hay impresora activa. El cobro se registró correctamente.',
            ];
        }

        if ($order === null && $sale->orderId !== null) {
            $order = $this->orders->findById($sale->orderId, $tenantId);
        }

        $key = $idempotencyKey ?? "sale_receipt:{$sale->id}:v1";

        $existing = $this->jobs->findByIdempotencyKey($tenantId, $branchId, $key);
        if ($existing !== null) {
            return ['job' => $existing, 'warning' => null];
        }

        $presentedOrder = $order !== null
            ? $this->presentation->presentOrder($order, $tenantId)
            : null;

        $waiterName = null;
        if ($order?->waiterUserId !== null) {
            $waiterName = $this->users->findDisplayNamesByIds([$order->waiterUserId])[$order->waiterUserId] ?? null;
        }

        $serviceAreaName = null;
        if ($order?->serviceAreaId !== null) {
            $area = $this->serviceAreas->findById($order->serviceAreaId, $tenantId, $branchId);
            $serviceAreaName = $area['name'] ?? null;
        }

        $cashierName = $this->users->findDisplayNamesByIds([$sale->cashierUserId])[$sale->cashierUserId] ?? null;
        $branchName = (string) (BranchModel::query()->where('id', $branchId)->value('name') ?? '');

        $salePayload = [
            'id' => $sale->id,
            'sale_number' => $sale->saleNumber,
            'order_id' => $sale->orderId,
            'order_number' => $presentedOrder['order_number'] ?? null,
            'table_label' => $presentedOrder['table_label'] ?? null,
            'total' => $sale->total,
            'currency' => $sale->currency,
            'payment_mode' => $sale->paymentMode,
            'paid_at' => $sale->paidAt,
            'payments' => array_map(static fn ($payment) => [
                'payment_method' => $payment->paymentMethod,
                'amount' => $payment->amount,
            ], $sale->payments),
        ];

        $payload = [
            'sale' => $salePayload,
            'order' => $presentedOrder,
            'waiter_name' => $waiterName,
            'cashier_name' => $cashierName,
            'service_area_name' => $serviceAreaName,
            'branch_name' => $branchName !== '' ? $branchName : null,
        ];

        $contentText = $this->contentBuilder->buildSaleReceipt(
            $salePayload,
            $presentedOrder,
            $cashierName,
            $waiterName,
            $serviceAreaName,
            $branchName !== '' ? $branchName : null,
        );

        $job = $this->jobs->create([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'device_id' => null,
            'type' => PrintJobType::SaleReceipt->value,
            'source_type' => PrintJobSourceType::Sale->value,
            'source_id' => $sale->id,
            'idempotency_key' => $key,
            'payload' => $payload,
            'content_text' => $contentText,
            'status' => PrintJobStatus::Pending->value,
            'requested_by_user_id' => $requestedByUserId,
        ]);

        $this->eventEmitter->emit(
            $tenantId,
            $branchId,
            'print_job.created',
            [
                'print_job_id' => $job['id'],
                'sale_id' => $sale->id,
                'order_id' => $sale->orderId,
                'type' => PrintJobType::SaleReceipt->value,
                'status' => PrintJobStatus::Pending->value,
            ],
        );

        return ['job' => $job, 'warning' => null];
    }
}
