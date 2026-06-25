<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Printing\UseCases\PrintSettlementPaymentUseCase;
use App\Application\StaffSettlement\DTOs\ListSettlementHistoryInput;
use App\Application\StaffSettlement\UseCases\ApplyManualDiscountUseCase;
use App\Application\StaffSettlement\UseCases\CancelManualDiscountUseCase;
use App\Application\StaffSettlement\UseCases\GenerateCurrentShiftSettlementsUseCase;
use App\Application\StaffSettlement\UseCases\GetCurrentShiftSettlementsUseCase;
use App\Application\StaffSettlement\UseCases\GetSettlementPayPreviewUseCase;
use App\Application\StaffSettlement\UseCases\GetSettlementPendingSourcesUseCase;
use App\Application\StaffSettlement\UseCases\GetSettlementUseCase;
use App\Application\StaffSettlement\UseCases\ListSettlementHistoryUseCase;
use App\Application\StaffSettlement\UseCases\MarkSettlementPaidUseCase;
use App\Application\StaffSettlement\UseCases\PreviewManualDiscountUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Settlement\ApplyManualDiscountRequest;
use App\Http\Requests\Api\V1\Settlement\MarkSettlementPaidRequest;
use App\Http\Requests\Api\V1\Settlement\PreviewManualDiscountRequest;
use App\Http\Requests\Api\V1\Settlement\PrintSettlementRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class SettlementController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentShiftSettlementsUseCase $getCurrent,
        private readonly GenerateCurrentShiftSettlementsUseCase $generate,
        private readonly GetSettlementUseCase $getSettlement,
        private readonly GetSettlementPayPreviewUseCase $payPreview,
        private readonly MarkSettlementPaidUseCase $markPaid,
        private readonly ListSettlementHistoryUseCase $listHistory,
        private readonly GetSettlementPendingSourcesUseCase $pendingSources,
        private readonly ApplyManualDiscountUseCase $applyManualDiscount,
        private readonly CancelManualDiscountUseCase $cancelManualDiscount,
        private readonly PreviewManualDiscountUseCase $previewManualDiscount,
        private readonly PrintSettlementPaymentUseCase $printSettlement,
    ) {
    }

    public function pendingSources(Request $request): JsonResponse
    {
        return $this->presenter->present($this->pendingSources->execute(
            (object) ['scope' => $request->query('scope')],
        ));
    }

    public function currentShift(Request $request): JsonResponse
    {
        return $this->presenter->present($this->getCurrent->execute(
            (object) ['scope' => $request->query('scope')],
        ));
    }

    public function generateCurrentShift(): JsonResponse
    {
        return $this->presenter->present($this->generate->execute(), 201);
    }

    public function show(int $id): JsonResponse
    {
        return $this->presenter->present($this->getSettlement->execute((object) ['settlementId' => $id]));
    }

    public function payPreview(int $id, Request $request): JsonResponse
    {
        $appliedFineIds = $request->query('applied_fine_ids', []);
        if (! is_array($appliedFineIds)) {
            $appliedFineIds = [$appliedFineIds];
        }

        return $this->presenter->present($this->payPreview->execute((object) [
            'settlementId' => $id,
            'appliedFineIds' => array_map('intval', $appliedFineIds),
        ]));
    }

    public function markPaid(int $id, MarkSettlementPaidRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->markPaid->execute((object) [
            'settlementId' => $id,
            'paymentMethod' => $validated['payment_method'],
            'notes' => $validated['notes'] ?? null,
            'appliedFineIds' => array_map('intval', $validated['applied_fine_ids'] ?? []),
        ]));
    }

    public function applyManualDiscount(int $id, ApplyManualDiscountRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->applyManualDiscount->execute((object) [
            'settlementId' => $id,
            'discountMode' => strtoupper($validated['discount_mode']),
            'discountValue' => (float) $validated['discount_value'],
            'reason' => $validated['reason'],
            'notes' => $validated['notes'] ?? null,
        ]));
    }

    public function previewManualDiscount(int $id, PreviewManualDiscountRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->previewManualDiscount->execute((object) [
            'settlementId' => $id,
            'discountMode' => strtoupper($validated['discount_mode']),
            'discountValue' => (float) $validated['discount_value'],
        ]));
    }

    public function cancelManualDiscount(int $id): JsonResponse
    {
        return $this->presenter->present($this->cancelManualDiscount->execute((object) [
            'settlementId' => $id,
        ]));
    }

    public function print(int $id, PrintSettlementRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return $this->presenter->present($this->printSettlement->execute((object) [
            'settlementId' => $id,
            'reprint' => (bool) ($validated['reprint'] ?? false),
        ]));
    }

    public function history(Request $request): JsonResponse
    {
        return $this->presenter->present($this->listHistory->execute(new ListSettlementHistoryInput(
            limit: (int) $request->query('limit', 50),
            officialShiftId: $request->query('official_shift_id') ? (int) $request->query('official_shift_id') : null,
            staffUserId: $request->query('staff_user_id') ? (int) $request->query('staff_user_id') : null,
            settlementType: $request->query('settlement_type'),
            status: $request->query('status'),
            dateFrom: $request->query('date_from'),
            dateTo: $request->query('date_to'),
        )));
    }
}
