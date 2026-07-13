<?php

declare(strict_types=1);

namespace App\Application\Cash\DTOs;

use JsonSerializable;

final readonly class FinancialDashboardDTO implements JsonSerializable
{
    /**
     * @param  array<string, mixed>  $sales_summary
     * @param  array<string, mixed>  $cash_summary
     * @param  array<string, mixed>  $movement_summary
     * @param  array<string, mixed>  $settlement_summary
     * @param  array<string, mixed>  $scope_summary
     * @param  array<string, mixed>  $financial_summary
     */
    public function __construct(
        public array $sales_summary,
        public array $cash_summary,
        public array $movement_summary,
        public array $settlement_summary,
        public array $scope_summary,
        public array $financial_summary,
    ) {
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function toArray(): array
    {
        return [
            'sales_summary' => $this->sales_summary,
            'cash_summary' => $this->cash_summary,
            'movement_summary' => $this->movement_summary,
            'settlement_summary' => $this->settlement_summary,
            'scope_summary' => $this->scope_summary,
            'financial_summary' => $this->financial_summary,
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
