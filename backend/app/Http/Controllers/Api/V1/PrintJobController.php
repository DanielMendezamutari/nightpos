<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Printing\UseCases\ClaimPrintJobUseCase;
use App\Application\Printing\UseCases\GetOrderPrintStatusUseCase;
use App\Application\Printing\UseCases\ListPendingPrintJobsUseCase;
use App\Application\Printing\UseCases\ListPrintJobsUseCase;
use App\Application\Printing\UseCases\MarkPrintJobFailedUseCase;
use App\Application\Printing\UseCases\MarkPrintJobPrintedUseCase;
use App\Application\Printing\UseCases\ReprintOrderCommandUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Printing\MarkPrintJobFailedRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class PrintJobController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly ListPendingPrintJobsUseCase $listPending,
        private readonly ClaimPrintJobUseCase $claim,
        private readonly MarkPrintJobPrintedUseCase $markPrinted,
        private readonly MarkPrintJobFailedUseCase $markFailed,
        private readonly ListPrintJobsUseCase $listJobs,
        private readonly ReprintOrderCommandUseCase $reprintOrder,
        private readonly GetOrderPrintStatusUseCase $orderPrintStatus,
    ) {
    }

    public function pending(Request $request): JsonResponse
    {
        return $this->presenter->present($this->listPending->execute((object) [
            'limit' => $request->integer('limit', 10),
        ]));
    }

    public function claim(int $id): JsonResponse
    {
        return $this->presenter->present($this->claim->execute((object) [
            'jobId' => $id,
        ]));
    }

    public function printed(int $id): JsonResponse
    {
        return $this->presenter->present($this->markPrinted->execute((object) [
            'jobId' => $id,
        ]));
    }

    public function failed(int $id, MarkPrintJobFailedRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->markFailed->execute((object) [
            'jobId' => $id,
            'error' => $validated['error'] ?? 'Error de impresión',
        ]));
    }

    public function index(Request $request): JsonResponse
    {
        return $this->presenter->present($this->listJobs->execute((object) [
            'status' => $request->query('status'),
            'limit' => $request->integer('limit', 50),
        ]));
    }

    public function reprintOrder(int $id): JsonResponse
    {
        return $this->presenter->present($this->reprintOrder->execute((object) [
            'orderId' => $id,
        ]), 201);
    }

    public function orderStatus(int $id): JsonResponse
    {
        return $this->presenter->present($this->orderPrintStatus->execute((object) [
            'orderId' => $id,
        ]));
    }
}
