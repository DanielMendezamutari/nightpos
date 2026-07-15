<?php

declare(strict_types=1);

namespace App\Application\Cash\Support;

final class LegacyFinancialSummaryMapper
{
    /**
     * @param  array<string, mixed>  $salesSummary
     * @param  array<string, mixed>  $cashSummary
     * @param  array<string, mixed>  $movementSummary
     * @return array<string, mixed>
     */
    public function map(
        array $salesSummary,
        array $cashSummary,
        array $movementSummary,
        ?string $storedExpectedAmount,
        ?string $declaredClosingAmount,
        ?string $differenceAmount,
        string $status,
    ): array {
        $salesByMethod = $salesSummary['sales_by_method'] ?? $salesSummary['by_method'] ?? [
            'cash' => '0.00',
            'qr' => '0.00',
            'card' => '0.00',
        ];

        $totalSales = (string) ($salesSummary['total_sales_amount'] ?? $salesSummary['total_sales'] ?? '0.00');

        $incomeByMethod = $movementSummary['income_by_payment_method'] ?? [
            'cash' => '0.00',
            'qr' => '0.00',
            'card' => '0.00',
        ];

        $expenseByMethod = $movementSummary['expense_by_payment_method'] ?? [
            'cash' => '0.00',
            'qr' => '0.00',
            'card' => '0.00',
        ];

        $openingCash = (string) ($cashSummary['opening_cash'] ?? '0.00');

        $expectedCash = $status === 'CLOSED' && $storedExpectedAmount !== null
            ? $this->asMoneyString($storedExpectedAmount)
            : $this->asMoneyString((string) ($cashSummary['expected_cash'] ?? '0.00'));

        $expectedQr = $this->asMoneyString(
            (float) ($incomeByMethod['qr'] ?? 0)
            - (float) ($expenseByMethod['qr'] ?? 0)
        );

        $expectedCard = $this->asMoneyString(
            (float) ($incomeByMethod['card'] ?? 0)
            - (float) ($expenseByMethod['card'] ?? 0)
        );

        return [
            'total_cash' => $this->asMoneyString((string) ($salesSummary['cash_total'] ?? $salesSummary['sales_cash'] ?? '0.00')),
            'total_qr' => $this->asMoneyString((string) ($salesSummary['qr_total'] ?? $salesSummary['sales_qr'] ?? '0.00')),
            'total_card' => $this->asMoneyString((string) ($salesSummary['card_total'] ?? $salesSummary['sales_card'] ?? '0.00')),
            'sales_by_method' => [
                'cash' => $this->asMoneyString((string) ($salesByMethod['cash'] ?? '0.00')),
                'qr' => $this->asMoneyString((string) ($salesByMethod['qr'] ?? '0.00')),
                'card' => $this->asMoneyString((string) ($salesByMethod['card'] ?? '0.00')),
            ],
            'by_method' => [
                'cash' => $this->asMoneyString((string) ($salesSummary['cash_total'] ?? $salesSummary['sales_cash'] ?? '0.00')),
                'qr' => $this->asMoneyString((string) ($salesSummary['qr_total'] ?? $salesSummary['sales_qr'] ?? '0.00')),
                'card' => $this->asMoneyString((string) ($salesSummary['card_total'] ?? $salesSummary['sales_card'] ?? '0.00')),
                'mixed' => $this->asMoneyString((string) ($salesSummary['mixed_total'] ?? '0.00')),
            ],
            'by_source' => $salesSummary['by_source'] ?? [
                'order_sales' => '0.00',
                'direct_sales' => '0.00',
                'room_services' => '0.00',
                'bracelets' => '0.00',
                'other_sales' => '0.00',
            ],
            'opening_cash' => $this->asMoneyString($openingCash),
            'income_cash' => $this->asMoneyString((string) ($incomeByMethod['cash'] ?? '0.00')),
            'income_qr' => $this->asMoneyString((string) ($incomeByMethod['qr'] ?? '0.00')),
            'income_card' => $this->asMoneyString((string) ($incomeByMethod['card'] ?? '0.00')),
            'expense_cash' => $this->asMoneyString((string) ($expenseByMethod['cash'] ?? '0.00')),
            'expense_qr' => $this->asMoneyString((string) ($expenseByMethod['qr'] ?? '0.00')),
            'expense_card' => $this->asMoneyString((string) ($expenseByMethod['card'] ?? '0.00')),
            'sales_cash' => $this->asMoneyString((string) ($salesSummary['cash_total'] ?? $salesSummary['sales_cash'] ?? '0.00')),
            'sales_qr' => $this->asMoneyString((string) ($salesSummary['qr_total'] ?? $salesSummary['sales_qr'] ?? '0.00')),
            'sales_card' => $this->asMoneyString((string) ($salesSummary['card_total'] ?? $salesSummary['sales_card'] ?? '0.00')),
            'expected_qr' => $expectedQr,
            'expected_card' => $expectedCard,
            'total_sales_amount' => $this->asMoneyString($totalSales),
            'total_sales' => $this->asMoneyString($totalSales),
            'total_manual_income' => $this->asMoneyString((string) ($movementSummary['income_by_family']['MANUAL'] ?? '0.00')),
            'total_manual_expense' => $this->asMoneyString((string) ($movementSummary['total_expense'] ?? '0.00')),
            'total_income' => $this->asMoneyString((string) ($movementSummary['total_income'] ?? '0.00')),
            'total_expense' => $this->asMoneyString((string) ($movementSummary['total_expense'] ?? '0.00')),
            'expected_cash' => $expectedCash,
            'counted_cash' => $declaredClosingAmount !== null ? $this->asMoneyString($declaredClosingAmount) : null,
            'cash_difference' => $differenceAmount !== null ? $this->asMoneyString($differenceAmount) : null,
            'income_by_method' => [
                'cash' => $this->asMoneyString((string) ($incomeByMethod['cash'] ?? '0.00')),
                'qr' => $this->asMoneyString((string) ($incomeByMethod['qr'] ?? '0.00')),
                'card' => $this->asMoneyString((string) ($incomeByMethod['card'] ?? '0.00')),
            ],
            'expense_by_method' => [
                'cash' => $this->asMoneyString((string) ($expenseByMethod['cash'] ?? '0.00')),
                'qr' => $this->asMoneyString((string) ($expenseByMethod['qr'] ?? '0.00')),
                'card' => $this->asMoneyString((string) ($expenseByMethod['card'] ?? '0.00')),
            ],
            'expected_by_method' => [
                'cash' => $expectedCash,
                'qr' => $expectedQr,
                'card' => $expectedCard,
            ],
        ];
    }

    private function asMoneyString(float|string $value): string
    {
        return number_format((float) $value, 2, '.', '');
    }
}
