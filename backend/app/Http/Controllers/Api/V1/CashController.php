<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Application\Cash\DTOs\CloseCashSessionInput;
use App\Application\Cash\DTOs\OpenCashSessionInput;
use App\Application\Cash\DTOs\RegisterCashMovementInput;
use App\Application\Cash\UseCases\CloseCashSessionUseCase;
use App\Application\Cash\UseCases\GetCashMovementUseCase;
use App\Application\Cash\UseCases\GetCashSessionUseCase;
use App\Application\Cash\UseCases\GetCashSessionCloseCheckUseCase;
use App\Application\Cash\UseCases\GetCurrentCashSessionUseCase;
use App\Application\Cash\UseCases\OpenCashSessionUseCase;
use App\Application\Cash\UseCases\RegisterCashMovementUseCase;
use App\Application\Printing\UseCases\PrintCashCloseUseCase;
use App\Application\Printing\UseCases\PrintCashMovementUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Cash\CloseCashSessionRequest;
use App\Http\Requests\Api\V1\Cash\OpenCashSessionRequest;
use App\Http\Requests\Api\V1\Cash\RegisterCashMovementRequest;
use App\Infrastructure\Presentation\Http\Contracts\ApiResponsePresenterInterface;
use Illuminate\Http\JsonResponse;

final class CashController extends Controller
{
    public function __construct(
        private readonly ApiResponsePresenterInterface $presenter,
        private readonly GetCurrentCashSessionUseCase $getCurrentSession,
        private readonly GetCashSessionCloseCheckUseCase $getCloseCheck,
        private readonly OpenCashSessionUseCase $openSession,
        private readonly RegisterCashMovementUseCase $registerMovement,
        private readonly CloseCashSessionUseCase $closeSession,
        private readonly GetCashMovementUseCase $getMovement,
        private readonly GetCashSessionUseCase $getSession,
        private readonly PrintCashMovementUseCase $printMovement,
        private readonly PrintCashCloseUseCase $printClose,
    ) {
    }

    public function current(): JsonResponse
    {
        return $this->presenter->present($this->getCurrentSession->execute());
    }

    public function closeCheck(): JsonResponse
    {
        return $this->presenter->present($this->getCloseCheck->execute());
    }

    public function open(OpenCashSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->openSession->execute(new OpenCashSessionInput(
            openingAmount: (string) $validated['opening_amount'],
            cashRegisterId: isset($validated['cash_register_id']) ? (int) $validated['cash_register_id'] : null,
            openingNotes: $validated['opening_notes'] ?? null,
        ));

        return $this->presenter->present($result, 201);
    }

    public function registerMovement(RegisterCashMovementRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->registerMovement->execute(new RegisterCashMovementInput(
            movementType: $validated['movement_type'],
            amount: (string) $validated['amount'],
            cashMovementReasonId: (int) $validated['cash_movement_reason_id'],
            notes: $validated['notes'] ?? null,
            description: $validated['description'] ?? null,
            paymentMethod: $validated['payment_method'] ?? 'CASH',
        ));

        return $this->presenter->present($result, 201);
    }

    public function close(CloseCashSessionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->closeSession->execute(new CloseCashSessionInput(
            declaredClosingAmount: (string) $validated['declared_closing_amount'],
            closingNotes: $validated['closing_notes'] ?? null,
        ));

        return $this->presenter->present($result);
    }

    public function showMovement(int $id): JsonResponse
    {
        return $this->presenter->present($this->getMovement->execute((object) ['movementId' => $id]));
    }

    public function showSession(int $id): JsonResponse
    {
        return $this->presenter->present($this->getSession->execute((object) ['sessionId' => $id]));
    }

    public function printMovement(int $id): JsonResponse
    {
        $reprint = (bool) request()->boolean('reprint');

        return $this->presenter->present($this->printMovement->execute((object) [
            'movementId' => $id,
            'reprint' => $reprint,
        ]));
    }

    public function printClose(int $id): JsonResponse
    {
        $reprint = (bool) request()->boolean('reprint');

        return $this->presenter->present($this->printClose->execute((object) [
            'sessionId' => $id,
            'reprint' => $reprint,
        ]));
    }
}
