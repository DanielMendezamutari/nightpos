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

        $totalSales = new Money((string) ($summary['total_sales'] ?? '0'));
        $salesCount = (int) ($summary['sales_count'] ?? 0);
        $averageTicket = $salesCount > 0 ? $totalSales->divide($salesCount) : new Money('0');

        $salesByMethod = $summary['sales_by_method'] ?? [];
        $salesCash = new Money((string) ($salesByMethod['cash'] ?? '0'));
        $salesQr = new Money((string) ($salesByMethod['qr'] ?? '0'));
        $salesCard = new Money((string) ($salesByMethod['card'] ?? '0'));

        return new SalesSummaryDTO(
            total_sales: $totalSales,
            sales_count: $salesCount,
            average_ticket: $averageTicket,
            sales_cash: $salesCash,
            sales_qr: $salesQr,
            sales_card: $salesCard,
            mixed_sales_count: (int) ($summary['sales_mixed_count'] ?? 0),
            sales_by_method: [
                'cash' => number_format((float) $salesCash->amount, 2, '.', ''),
                'qr' => number_format((float) $salesQr->amount, 2, '.', ''),
                'card' => number_format((float) $salesCard->amount, 2, '.', ''),
            ],
            products_sold_count: (int) ($summary['products_sold_count'] ?? 0),
            services_sold_count: (int) ($summary['services_sold_count'] ?? 0),
        );
    }
}
