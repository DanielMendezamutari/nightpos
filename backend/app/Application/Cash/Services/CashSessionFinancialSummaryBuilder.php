<?php

declare(strict_types=1);

namespace App\Application\Cash\Services;

use App\Domain\Cash\Repositories\CashSessionRepositoryInterface;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\SalePaymentModel;

final class CashSessionFinancialSummaryBuilder
{
    public function __construct(
        private readonly CashSessionRepositoryInterface $cashSessions,
        private readonly SaleRepositoryInterface $sales,
    ) {
    }

    /**
     * @return array<string, string|null>
     */
    public function build(
        int $sessionId,
        string $openingAmount,
        ?string $storedExpectedAmount,
        ?string $declaredClosingAmount,
        ?string $differenceAmount,
        string $status,
    ): array {
        $salesByMethod = $this->sales->sumPaymentsByMethodForSession($sessionId);
        $manual = $this->cashSessions->sumManualMovements($sessionId);
        $movementTotals = $this->cashSessions->sumMovements($sessionId);

        $totalCash = (float) $salesByMethod['cash'];
        $totalQr = (float) $salesByMethod['qr'];
        $totalCard = (float) $salesByMethod['card'];

        $totalSales = (float) SalePaymentModel::query()
            ->whereIn('sale_id', function ($query) use ($sessionId) {
                $query->select('id')
                    ->from('sales')
                    ->where('cash_session_id', $sessionId);
            })
            ->sum('amount');

        $computedExpected = (float) $openingAmount + $totalCash + (float) $manual['income'] - (float) $manual['expense'];

        $expectedCash = $status === 'CLOSED' && $storedExpectedAmount !== null
            ? $storedExpectedAmount
            : number_format($computedExpected, 2, '.', '');

        return [
            'total_cash' => $salesByMethod['cash'],
            'total_qr' => $salesByMethod['qr'],
            'total_card' => $salesByMethod['card'],
            'total_sales' => number_format($totalSales, 2, '.', ''),
            'total_manual_income' => $manual['income'],
            'total_manual_expense' => $manual['expense'],
            'total_income' => $movementTotals['income'],
            'total_expense' => $movementTotals['expense'],
            'expected_cash' => $expectedCash,
            'counted_cash' => $declaredClosingAmount,
            'cash_difference' => $differenceAmount,
        ];
    }

    /**
     * @return array<string, string>
     */
    public function salesByMethod(int $sessionId): array
    {
        return $this->sales->sumPaymentsByMethodForSession($sessionId);
    }
}
