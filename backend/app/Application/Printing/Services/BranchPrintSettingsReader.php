<?php

declare(strict_types=1);

namespace App\Application\Printing\Services;

use App\Infrastructure\Persistence\Eloquent\Models\BranchModel;

final class BranchPrintSettingsReader
{
    public function isAutoPrintOrderCommandEnabled(int $branchId): bool
    {
        $value = BranchModel::query()
            ->where('id', $branchId)
            ->value('auto_print_order_command');

        return $value === null ? true : (bool) $value;
    }
}
