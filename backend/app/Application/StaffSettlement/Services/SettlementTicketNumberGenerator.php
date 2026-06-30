<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Shared\Domain\Enums\DocumentSequenceType;

final class SettlementTicketNumberGenerator
{
    public function __construct(
        private readonly DocumentSequenceService $sequences,
    ) {
    }

    public function next(int $tenantId, int $branchId): string
    {
        $branch = BranchModel::query()->findOrFail($branchId);
        $prefix = strtoupper(trim((string) ($branch->code ?: ('B'.$branchId))));
        $year = (int) now()->format('Y');
        $periodKey = (string) $year;

        $sequence = $this->sequences->reserveNext(
            tenantId: $tenantId,
            branchId: $branchId,
            documentType: DocumentSequenceType::SettlementPayment,
            periodKey: $periodKey,
        );

        return sprintf('%s-%d-%06d', $prefix, $year, $sequence);
    }
}
