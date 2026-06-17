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

     * @return array<string, string|null|array<string, string>>

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

        $byMethod = $this->cashSessions->sumMovementsByMethod($sessionId);



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



        $cashMovements = $byMethod['CASH'];

        $qrMovements = $byMethod['QR'];

        $cardMovements = $byMethod['CARD'];



        $computedExpectedCash = (float) $openingAmount

            + (float) $cashMovements['income']

            - (float) $cashMovements['expense'];



        $computedExpectedQr = (float) $qrMovements['income'] - (float) $qrMovements['expense'];

        $computedExpectedCard = (float) $cardMovements['income'] - (float) $cardMovements['expense'];



        $expectedCash = $status === 'CLOSED' && $storedExpectedAmount !== null

            ? $storedExpectedAmount

            : number_format($computedExpectedCash, 2, '.', '');



        return [

            'total_cash' => $salesByMethod['cash'],

            'total_qr' => $salesByMethod['qr'],

            'total_card' => $salesByMethod['card'],

            'sales_by_method' => [

                'cash' => $salesByMethod['cash'],

                'qr' => $salesByMethod['qr'],

                'card' => $salesByMethod['card'],

            ],

            'opening_cash' => $openingAmount,

            'income_cash' => $cashMovements['income'],

            'income_qr' => $qrMovements['income'],

            'income_card' => $cardMovements['income'],

            'expense_cash' => $cashMovements['expense'],

            'expense_qr' => $qrMovements['expense'],

            'expense_card' => $cardMovements['expense'],

            'sales_cash' => $salesByMethod['cash'],

            'sales_qr' => $salesByMethod['qr'],

            'sales_card' => $salesByMethod['card'],

            'expected_qr' => number_format($computedExpectedQr, 2, '.', ''),

            'expected_card' => number_format($computedExpectedCard, 2, '.', ''),

            'total_sales' => number_format($totalSales, 2, '.', ''),

            'total_manual_income' => $manual['income'],

            'total_manual_expense' => $manual['expense'],

            'total_income' => $movementTotals['income'],

            'total_expense' => $movementTotals['expense'],

            'expected_cash' => $expectedCash,

            'counted_cash' => $declaredClosingAmount,

            'cash_difference' => $differenceAmount,

            'income_by_method' => [

                'cash' => $cashMovements['income'],

                'qr' => $qrMovements['income'],

                'card' => $cardMovements['income'],

            ],

            'expense_by_method' => [

                'cash' => $cashMovements['expense'],

                'qr' => $qrMovements['expense'],

                'card' => $cardMovements['expense'],

            ],

            'expected_by_method' => [

                'cash' => $expectedCash,

                'qr' => number_format($computedExpectedQr, 2, '.', ''),

                'card' => number_format($computedExpectedCard, 2, '.', ''),

            ],

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


