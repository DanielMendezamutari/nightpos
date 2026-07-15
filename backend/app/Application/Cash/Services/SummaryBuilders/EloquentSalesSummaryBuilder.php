<?php

declare(strict_types=1);

namespace App\Application\Cash\Services\SummaryBuilders;

use App\Application\Cash\DTOs\SalesSummaryDTO;
use App\Domain\Cash\Contracts\SummaryBuilders\SalesSummaryBuilder;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Domain\Sale\Repositories\SaleRepositoryInterface;
use App\Shared\Domain\ValueObjects\Money;

final readonly class EloquentSalesSummaryBuilder implements SalesSummaryBuilder
{
    public function __construct(
        private SaleRepositoryInterface $saleRepository,
    ) {
    }

    public function build(CashSessionId $cashSessionId): SalesSummaryDTO
    {
        $summary = $this->saleRepository->getSalesSummary($cashSessionId);

        $totalSales = new Money((string) ($summary['total_sales_amount'] ?? $summary['total_sales'] ?? '0'));
        $salesCount = (int) ($summary['sales_count'] ?? 0);
        $averageTicket = $salesCount > 0 ? $totalSales->divide($salesCount) : new Money('0');

        $salesByMethod = $summary['by_method'] ?? ($summary['sales_by_method'] ?? []);
        $salesCash = new Money((string) ($summary['cash_total'] ?? $salesByMethod['cash'] ?? '0'));
        $salesQr = new Money((string) ($summary['qr_total'] ?? $salesByMethod['qr'] ?? '0'));
        $salesCard = new Money((string) ($summary['card_total'] ?? $salesByMethod['card'] ?? '0'));
        $salesMixed = new Money((string) ($summary['mixed_total'] ?? $salesByMethod['mixed'] ?? '0'));

        $bySource = $summary['by_source'] ?? [];
        $normalizedByMethod = [
            'cash' => number_format((float) $salesCash->amount, 2, '.', ''),
            'qr' => number_format((float) $salesQr->amount, 2, '.', ''),
            'card' => number_format((float) $salesCard->amount, 2, '.', ''),
            'mixed' => number_format((float) $salesMixed->amount, 2, '.', ''),
        ];

        return new SalesSummaryDTO(
            total_sales_amount: $totalSales,
            sales_count: $salesCount,
            average_ticket: $averageTicket,
            cash_total: $salesCash,
            qr_total: $salesQr,
            card_total: $salesCard,
            mixed_total: $salesMixed,
            mixed_sales_count: (int) ($summary['sales_mixed_count'] ?? 0),
            by_source: [
                'order_sales' => (string) ($bySource['order_sales'] ?? '0.00'),
                'direct_sales' => (string) ($bySource['direct_sales'] ?? '0.00'),
                'room_services' => (string) ($bySource['room_services'] ?? '0.00'),
                'bracelets' => (string) ($bySource['bracelets'] ?? '0.00'),
                'other_sales' => (string) ($bySource['other_sales'] ?? '0.00'),
            ],
            by_method: $normalizedByMethod,
            sales_by_method: [
                'cash' => $normalizedByMethod['cash'],
                'qr' => $normalizedByMethod['qr'],
                'card' => $normalizedByMethod['card'],
            ],
            products_sold_count: (int) ($summary['products_sold_count'] ?? 0),
            services_sold_count: (int) ($summary['services_sold_count'] ?? 0),
        );
    }
}
