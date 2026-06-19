<?php

declare(strict_types=1);

namespace App\Application\Printing\UseCases;

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
    ) {
    }

    public function execute(?object $input = null): OperationResult
    {
        $tenant = $this->tenantContext->tenant();
        $branch = $this->branchContext->branch();

        if ($tenant === null || $branch === null) {
            throw PrintingDomainException::branchRequired();
        }

        if (isset($input->autoPrintOrderCommand)) {
            BranchModel::query()
                ->where('id', $branch->id)
                ->where('tenant_id', $tenant->id)
                ->update([
                    'auto_print_order_command' => (bool) $input->autoPrintOrderCommand,
                ]);
        }

        return OperationResult::ok('Configuración actualizada.', [
            'auto_print_order_command' => (bool) BranchModel::query()
                ->where('id', $branch->id)
                ->value('auto_print_order_command'),
        ]);
    }
}
