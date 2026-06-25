<?php

declare(strict_types=1);

namespace App\Application\Printing\Services;

use App\Application\Reports\Services\CashCloseReportSectionsBuilder;
use App\Domain\Reports\Repositories\ReportReadRepositoryInterface;
use App\Domain\Shift\ValueObjects\ShiftType;
use App\Infrastructure\Persistence\Eloquent\Models\OfficialShiftModel;
use App\Infrastructure\Persistence\Eloquent\Models\TenantModel;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

final class CashClosePrintPayloadEnricher
{
    public function __construct(
        private readonly ReportReadRepositoryInterface $reports,
        private readonly CashCloseReportSectionsBuilder $sections,
    ) {
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function enrich(array $payload, int $tenantId, int $branchId): array
    {
        $session = $payload['session'] ?? [];
        $sessionId = (int) ($session['id'] ?? 0);
        $shiftId = isset($session['official_shift_id']) ? (int) $session['official_shift_id'] : null;
        $summary = $payload['summary'] ?? [];

        $tenant = TenantModel::query()->find($tenantId);
        if ($tenant !== null) {
            $payload['tenant_name'] = (string) ($tenant->name ?? '');
        }

        if ($shiftId !== null && $shiftId > 0) {
            $shift = OfficialShiftModel::query()->find($shiftId);
            if ($shift !== null) {
                $type = ShiftType::fromString((string) $shift->shift_type);
                $name = trim((string) ($shift->name ?? ''));
                $payload['shift_label'] = $name !== ''
                    ? $name
                    : $type->label().' · '.(string) $shift->business_date;

                $adminId = $shift->closed_by_user_id ?? $shift->opened_by_user_id;
                if ($adminId !== null) {
                    $payload['admin_name'] = (string) (UserModel::query()->whereKey($adminId)->value('name') ?? '');
                }
            }
        }

        if ($sessionId <= 0) {
            return $payload;
        }

        $totalSales = (string) ($summary['total_sales'] ?? '0.00');
        $operational = $this->sections->forSession(
            $tenantId,
            $branchId,
            $sessionId,
            $shiftId,
            $totalSales,
        );

        $payload['operational'] = $operational;
        $payload['duration_minutes'] = $this->durationMinutes(
            (string) ($session['opened_at'] ?? ''),
            (string) ($session['closed_at'] ?? ''),
        );

        $recon = $this->reports->getProductReconciliation($tenantId, $branchId, [
            'cash_session_id' => $sessionId,
        ]);

        $sold = $recon['sold'] ?? [];
        usort(
            $sold,
            static fn (array $a, array $b): int => (float) ($b['total_amount'] ?? 0) <=> (float) ($a['total_amount'] ?? 0)
                ?: (int) ($b['quantity_sold'] ?? 0) <=> (int) ($a['quantity_sold'] ?? 0),
        );

        $payload['top_products'] = array_map(
            static fn (array $row): array => [
                'product_name' => (string) ($row['product_name'] ?? 'Producto'),
                'quantity_sold' => (int) ($row['quantity_sold'] ?? 0),
                'total_amount' => (string) ($row['total_amount'] ?? '0.00'),
            ],
            array_slice($sold, 0, 5),
        );

        $payload['reconciliation_mismatch_count'] = (int) ($recon['summary']['mismatch_count'] ?? 0);

        $issues = [];
        foreach ($recon['comparison'] ?? [] as $row) {
            $status = (string) ($row['status'] ?? '');
            if (in_array($status, ['OK', 'DIRECT_SALE_ONLY'], true)) {
                continue;
            }

            $issues[] = ((string) ($row['product_name'] ?? 'Producto')).': '.$status;
        }

        $payload['reconciliation_issues'] = array_slice($issues, 0, 6);

        return $payload;
    }

    private function durationMinutes(string $openedAt, string $closedAt): ?int
    {
        if ($openedAt === '' || $closedAt === '') {
            return null;
        }

        $start = strtotime($openedAt);
        $end = strtotime($closedAt);

        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return (int) round(($end - $start) / 60);
    }
}
