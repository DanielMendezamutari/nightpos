<?php

declare(strict_types=1);

namespace App\Application\StaffSettlement\Services;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;

final class SettlementTicketNumberGenerator
{
    public function next(int $branchId): string
    {
        $branch = BranchModel::query()->findOrFail($branchId);
        $prefix = strtoupper(trim((string) ($branch->code ?: ('B'.$branchId))));
        $year = (int) now()->format('Y');
        $pattern = $prefix.'-'.$year.'-%';

        $lastTicket = StaffSettlementModel::query()
            ->where('branch_id', $branchId)
            ->whereNotNull('ticket_number')
            ->where('ticket_number', 'like', $pattern)
            ->orderByDesc('ticket_number')
            ->value('ticket_number');

        $sequence = 1;

        if (is_string($lastTicket) && preg_match('/-(\d{6})$/', $lastTicket, $matches) === 1) {
            $sequence = (int) $matches[1] + 1;
        }

        return sprintf('%s-%d-%06d', $prefix, $year, $sequence);
    }
}
