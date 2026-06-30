<?php

declare(strict_types=1);

namespace App\Application\DocumentSequence\Services;

use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class DocumentSequenceService
{
    /**
     * Reserva el siguiente correlativo entero (1-based) de forma atómica.
     * Debe invocarse dentro de una transacción DB del use case.
     */
    public function reserveNext(
        int $tenantId,
        int $branchId,
        DocumentSequenceType $documentType,
        string $periodKey,
    ): int {
        if (DB::getDriverName() === 'mysql') {
            return $this->reserveNextWithUpsert($tenantId, $branchId, $documentType, $periodKey);
        }

        return $this->reserveNextWithLock($tenantId, $branchId, $documentType, $periodKey);
    }

    /**
     * Inicializa document_sequences desde tickets de liquidación ya pagados.
     */
    public function syncSettlementPaymentSequencesFromExistingTickets(): void
    {
        $maxByKey = [];

        StaffSettlementModel::query()
            ->whereNotNull('ticket_number')
            ->select(['tenant_id', 'branch_id', 'ticket_number'])
            ->orderBy('id')
            ->chunk(500, function ($rows) use (&$maxByKey): void {
                foreach ($rows as $row) {
                    if (! is_string($row->ticket_number)) {
                        continue;
                    }

                    if (preg_match('/-(\d{4})-(\d{6})$/', $row->ticket_number, $matches) !== 1) {
                        continue;
                    }

                    $periodKey = $matches[1];
                    $sequence = (int) $matches[2];
                    $key = sprintf(
                        '%d:%d:%s:%s',
                        (int) $row->tenant_id,
                        (int) $row->branch_id,
                        DocumentSequenceType::SettlementPayment->value,
                        $periodKey,
                    );

                    $maxByKey[$key] = max($maxByKey[$key] ?? 0, $sequence);
                }
            });

        foreach ($maxByKey as $key => $lastValue) {
            [$tenantId, $branchId, $documentType, $periodKey] = explode(':', $key, 4);

            DocumentSequenceModel::query()->updateOrCreate(
                [
                    'tenant_id' => (int) $tenantId,
                    'branch_id' => (int) $branchId,
                    'document_type' => $documentType,
                    'period_key' => $periodKey,
                ],
                [
                    'last_value' => $lastValue,
                ],
            );
        }
    }

    public function currentValue(
        int $tenantId,
        int $branchId,
        DocumentSequenceType $documentType,
        string $periodKey,
    ): ?int {
        $value = DocumentSequenceModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->where('document_type', $documentType->value)
            ->where('period_key', $periodKey)
            ->value('last_value');

        return $value === null ? null : (int) $value;
    }

    private function reserveNextWithUpsert(
        int $tenantId,
        int $branchId,
        DocumentSequenceType $documentType,
        string $periodKey,
    ): int {
        $type = $documentType->value;
        $now = now();

        DB::insert(
            'INSERT INTO document_sequences (tenant_id, branch_id, document_type, period_key, last_value, created_at, updated_at)
             VALUES (?, ?, ?, ?, LAST_INSERT_ID(1), ?, ?)
             ON DUPLICATE KEY UPDATE
                last_value = LAST_INSERT_ID(last_value + 1),
                updated_at = VALUES(updated_at)',
            [$tenantId, $branchId, $type, $periodKey, $now, $now],
        );

        return (int) DB::getPdo()->lastInsertId();
    }

    private function reserveNextWithLock(
        int $tenantId,
        int $branchId,
        DocumentSequenceType $documentType,
        string $periodKey,
    ): int {
        $criteria = [
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'document_type' => $documentType->value,
            'period_key' => $periodKey,
        ];

        $row = DocumentSequenceModel::query()
            ->where($criteria)
            ->lockForUpdate()
            ->first();

        if ($row !== null) {
            $next = $row->last_value + 1;
            $row->update(['last_value' => $next]);

            return $next;
        }

        try {
            DocumentSequenceModel::query()->create([
                ...$criteria,
                'last_value' => 1,
            ]);

            return 1;
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }

            $row = DocumentSequenceModel::query()
                ->where($criteria)
                ->lockForUpdate()
                ->firstOrFail();

            $next = $row->last_value + 1;
            $row->update(['last_value' => $next]);

            return $next;
        }
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        if ((string) $exception->getCode() === '23000') {
            return true;
        }

        $message = $exception->getMessage();

        return str_contains($message, 'UNIQUE constraint failed')
            || str_contains($message, 'Duplicate entry')
            || str_contains($message, 'document_sequences_scope_unique');
    }
}
