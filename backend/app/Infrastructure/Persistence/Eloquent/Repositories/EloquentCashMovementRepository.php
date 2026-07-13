<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Cash\Contracts\CashMovementRepositoryInterface;
use App\Domain\Cash\ValueObjects\CashSessionId;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use Illuminate\Support\Facades\DB;

final class EloquentCashMovementRepository implements CashMovementRepositoryInterface
{
    public function getCashMovementsSummary(CashSessionId $cashSessionId): array
    {
        $incomes = CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId->value)
            ->where('movement_type', 'INCOME')
            ->select(
                DB::raw('SUM(amount) as total_incomes'),
                DB::raw('COUNT(*) as incomes_count'),
                DB::raw("SUM(CASE WHEN UPPER(payment_method) = 'CASH' THEN amount ELSE 0 END) as incomes_cash"),
                DB::raw("SUM(CASE WHEN UPPER(payment_method) = 'QR' THEN amount ELSE 0 END) as incomes_qr"),
                DB::raw("SUM(CASE WHEN UPPER(payment_method) = 'CARD' THEN amount ELSE 0 END) as incomes_card")
            )
            ->first()
            ->toArray();

        $expenses = CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId->value)
            ->where('movement_type', 'EXPENSE')
            ->select(
                DB::raw('SUM(amount) as total_expenses'),
                DB::raw('COUNT(*) as expenses_count'),
                DB::raw("SUM(CASE WHEN UPPER(payment_method) = 'CASH' THEN amount ELSE 0 END) as expenses_cash"),
                DB::raw("SUM(CASE WHEN UPPER(payment_method) = 'QR' THEN amount ELSE 0 END) as expenses_qr"),
                DB::raw("SUM(CASE WHEN UPPER(payment_method) = 'CARD' THEN amount ELSE 0 END) as expenses_card")
            )
            ->first()
            ->toArray();

        return [
            'total_incomes' => $incomes['total_incomes'] ?? '0',
            'incomes_count' => $incomes['incomes_count'] ?? 0,
            'incomes_cash' => $incomes['incomes_cash'] ?? '0',
            'incomes_qr' => $incomes['incomes_qr'] ?? '0',
            'incomes_card' => $incomes['incomes_card'] ?? '0',
            'total_expenses' => $expenses['total_expenses'] ?? '0',
            'expenses_count' => $expenses['expenses_count'] ?? 0,
            'expenses_cash' => $expenses['expenses_cash'] ?? '0',
            'expenses_qr' => $expenses['expenses_qr'] ?? '0',
            'expenses_card' => $expenses['expenses_card'] ?? '0',
        ];
    }

    public function getMovementsBreakdown(CashSessionId $cashSessionId): array
    {
        $rows = CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId->value)
            ->selectRaw('movement_family, movement_category, movement_type, UPPER(payment_method) as payment_method, COUNT(*) as cnt, SUM(amount) as total')
            ->groupBy('movement_family', 'movement_category', 'movement_type', DB::raw('UPPER(payment_method)'))
            ->get()
            ->map(fn (CashMovementModel $row) => [
                'movement_family' => $row->getAttribute('movement_family'),
                'movement_category' => $row->getAttribute('movement_category'),
                'movement_type' => $row->getAttribute('movement_type'),
                'payment_method' => $row->getAttribute('payment_method'),
                'cnt' => (int) $row->getAttribute('cnt'),
                'total' => (string) $row->getAttribute('total'),
            ])
            ->toArray();

        $timelineGroups = [];
        $timelineMovements = CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId->value)
            ->orderBy('created_at')
            ->get(['movement_type', 'amount', 'created_at']);

        foreach ($timelineMovements as $movement) {
            $createdAt = $movement->getAttribute('created_at');
            if ($createdAt === null) {
                continue;
            }

            $bucket = $createdAt->format('Y-m-d H:00:00');
            $movementType = strtoupper((string) $movement->getAttribute('movement_type'));
            $key = $bucket.'|'.$movementType;

            if (! isset($timelineGroups[$key])) {
                $timelineGroups[$key] = [
                    'hour_bucket' => $bucket,
                    'movement_type' => $movementType,
                    'cnt' => 0,
                    'total' => 0.0,
                ];
            }

            $timelineGroups[$key]['cnt']++;
            $timelineGroups[$key]['total'] += (float) $movement->getAttribute('amount');
        }

        $timelineRows = array_map(
            static fn (array $row) => [
                'hour_bucket' => $row['hour_bucket'],
                'movement_type' => $row['movement_type'],
                'cnt' => $row['cnt'],
                'total' => number_format($row['total'], 2, '.', ''),
            ],
            array_values($timelineGroups)
        );

        $lastMovement = CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId->value)
            ->orderByDesc('id')
            ->first(['id', 'movement_type', 'movement_family', 'movement_category', 'amount', 'created_at']);

        $maxMovement = CashMovementModel::query()
            ->where('cash_session_id', $cashSessionId->value)
            ->orderByDesc('amount')
            ->first(['id', 'movement_type', 'movement_family', 'movement_category', 'amount']);

        return [
            'rows' => $rows,
            'timeline_rows' => $timelineRows,
            'last_movement' => $lastMovement?->toArray(),
            'max_movement' => $maxMovement?->toArray(),
        ];
    }
}
