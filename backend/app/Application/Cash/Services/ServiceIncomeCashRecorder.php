<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Domain\Cash\Entities\CashMovement;
use App\Domain\Cash\Entities\CashSession;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashMovementType;
use App\Domain\GirlIncome\Exceptions\GirlIncomeDomainException;
use App\Domain\Sale\Exceptions\SaleDomainException;
use App\Domain\Sale\ValueObjects\PaymentMethod;
use App\Domain\Settings\Repositories\PaymentMethodRepositoryInterface;

final class ServiceIncomeCashRecorder
{
    public function __construct(
        private readonly OpenCashSessionResolver $cashSessionResolver,
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly PaymentMethodRepositoryInterface $paymentMethods,
    ) {
    }

    public function requireOpenSession(int $tenantId, int $branchId, int $userId): CashSession
    {
        $session = $this->cashSessionResolver->findOpenForCurrentUser($tenantId, $branchId, $userId);

        if ($session === null) {
            throw GirlIncomeDomainException::cashSessionRequired();
        }

        return $session;
    }

    public function normalizePaymentMethod(int $tenantId, int $branchId, string $methodCode): string
    {
        $code = strtoupper(trim($methodCode));
        $allowed = $this->paymentMethods->enabledLegacyCodes($tenantId, $branchId);

        if ($allowed === []) {
            $allowed = ['CASH', 'QR', 'CARD'];
        }

        if (! in_array($code, $allowed, true)) {
            throw SaleDomainException::invalidPaymentMethod($code);
        }

        return PaymentMethod::fromString($code)->value;
    }

    public function recordIncome(
        int $tenantId,
        int $branchId,
        CashSession $session,
        string $amount,
        string $paymentMethod,
        string $description,
        string $sourceType,
        int $sourceId,
        int $createdByUserId,
    ): CashMovement {
        return $this->cashSessions->addMovement(
            tenantId: $tenantId,
            branchId: $branchId,
            cashSessionId: $session->id,
            movementType: CashMovementType::INCOME,
            amount: $amount,
            description: $description,
            paymentMethod: $paymentMethod,
            createdByUserId: $createdByUserId,
            cashMovementReasonId: null,
            notes: null,
            sourceType: $sourceType,
            sourceId: $sourceId,
        );
    }
}
