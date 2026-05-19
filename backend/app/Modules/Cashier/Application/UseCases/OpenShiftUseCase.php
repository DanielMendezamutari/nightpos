<?php

declare(strict_types=1);

namespace App\Modules\Cashier\Application\UseCases;

use App\Modules\Cashier\Domain\Ports\ShiftRepository;

final readonly class OpenShiftUseCase
{
    public function __construct(private ShiftRepository $shiftRepository)
    {
    }

    public function execute(int $cashierUserId, int $siteId, string $period, int $openingCash): int
    {
        return $this->shiftRepository->open([
            'cashier_user_id' => $cashierUserId,
            'site_id' => $siteId,
            'period' => $period,
            'opening_cash' => $openingCash,
            'opened_at' => now(),
            'status' => 'open',
        ]);
    }
}
