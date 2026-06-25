<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

use App\Application\Printing\Services\BranchPrintSettingsReader;
use App\Domain\Printing\Exceptions\PrintingDomainException;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Shared\Application\DTOs\OperationResult;
use App\Shared\Contracts\BranchContextInterface;
use App\Shared\Contracts\TenantContextInterface;
use App\Shared\Contracts\UseCaseInterface;

final class UpdatePrintSettingsUseCase implements UseCaseInterface
{
    public function __construct(
        private readonly TenantContextInterface $tenantContext,
        private readonly BranchContextInterface $branchContext,
        private readonly BranchPrintSettingsReader $branchSettings,
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        $updates = [];

        if (isset($input->autoPrintOrderCommand)) {
            $updates['auto_print_order_command'] = (bool) $input->autoPrintOrderCommand;
        }

        if (isset($input->autoPrintSaleReceipt)) {
            $updates['auto_print_sale_receipt'] = (bool) $input->autoPrintSaleReceipt;
        }

        if ($updates !== []) {
            BranchModel::query()
                ->where('id', $branch->id)
                ->where('tenant_id', $tenant->id)
                ->update($updates);
        }

        return OperationResult::ok('Configuración actualizada.', [
            'auto_print_order_command' => $this->branchSettings->isAutoPrintOrderCommandEnabled($branch->id),
            'auto_print_sale_receipt' => $this->branchSettings->isAutoPrintSaleReceiptEnabled($branch->id),
        ]);
    }
}
