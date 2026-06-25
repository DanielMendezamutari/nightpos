<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Cash\Support\CashMapper;
use App\Domain\Cash\Exceptions\CashDomainException;
use App\Domain\Cash\Exceptions\CashSessionNotFoundException;
use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\AuthenticatedStaffContextInterface;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class PrintCashCloseUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly AuthenticatedStaffContextInterface $staffContext,
        private readonly CashSessionRepositoryInterface $sessions,
        private readonly CreateCashClosePrintJobUseCase $createPrintJob,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();
        $userId = $this->staffContext->userId();

        if ($tenant === null || $branch === null || $userId === null) {
            throw CashDomainException::branchRequired();
        }

        $sessionId = (int) ($input->sessionId ?? 0);
        $reprint = (bool) ($input->reprint ?? false);
        $session = $this->sessions->findById($sessionId, $tenant->id);

        if ($session === null || $session->branchId !== $branch->id) {
            throw new CashSessionNotFoundException();
        }

        $idempotencyKey = $reprint
            ? "cash_close:{$sessionId}:reprint:".now()->timestamp
            : "cash_close:{$sessionId}:v1";

        $printResult = $this->createPrintJob->execute(
            session: $session,
            tenantId: $tenant->id,
            branchId: $branch->id,
            requestedByUserId: $userId,
            idempotencyKey: $idempotencyKey,
        );

        return OperationResult::ok('Comprobante de cierre encolado.', [
            'session' => CashMapper::session($session),
            'print_job' => $printResult['job'],
            'print_warning' => $printResult['warning'],
        ]);
    }
}
