<?php

declare(strict_types=1);

namespace App\Application\DocumentSequence\Services;

use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Illuminate\Database\QueryException;

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
        $row = $this->lockOrCreateSequenceRow($tenantId, $branchId, $documentType, $periodKey);

        return $this->incrementLockedRow($row, $tenantId, $branchId, $documentType, $periodKey);
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
                    $parsed = $this->parseSettlementTicketSequence(
                        is_string($row->ticket_number) ? $row->ticket_number : null,
                    );

                    if ($parsed === null) {
                        continue;
                    }

                    [$periodKey, $sequence] = $parsed;
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

    /**
     * Máximo correlativo ya emitido en tickets de liquidación para el alcance dado.
     */
    public function maxSettlementTicketSequence(
        int $tenantId,
        int $branchId,
        string $periodKey,
    ): int {
        $suffix = '-'.$periodKey.'-';
        $max = 0;

        StaffSettlementModel::query()
            ->where('tenant_id', $tenantId)
            ->where('branch_id', $branchId)
            ->whereNotNull('ticket_number')
            ->where('ticket_number', 'like', '%'.$suffix.'%')
            ->select(['ticket_number'])
            ->orderBy('id')
            ->cursor()
            ->each(function (StaffSettlementModel $row) use ($periodKey, &$max): void {
                $parsed = $this->parseSettlementTicketSequence(
                    is_string($row->ticket_number) ? $row->ticket_number : null,
                    $periodKey,
                );

                if ($parsed !== null) {
                    [, $sequence] = $parsed;
                    $max = max($max, $sequence);
                }
            });

        return $max;
    }

    private function lockOrCreateSequenceRow(
        int $tenantId,
        int $branchId,
        DocumentSequenceType $documentType,
        string $periodKey,
    ): DocumentSequenceModel {
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
            return $row;
        }

        try {
            DocumentSequenceModel::query()->create([
                ...$criteria,
                'last_value' => 0,
            ]);
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }
        }

        return DocumentSequenceModel::query()
            ->where($criteria)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function incrementLockedRow(
        DocumentSequenceModel $row,
        int $tenantId,
        int $branchId,
        DocumentSequenceType $documentType,
        string $periodKey,
    ): int {
        $current = (int) $row->last_value;

        if ($documentType === DocumentSequenceType::SettlementPayment) {
            $maxIssued = $this->maxSettlementTicketSequence($tenantId, $branchId, $periodKey);

            if ($current < $maxIssued) {
                $current = $maxIssued;
            }
        }

        $next = $current + 1;
        $row->update(['last_value' => $next]);

        return $next;
    }

    /**
     * @return array{0: string, 1: int}|null [periodKey, sequence]
     */
    private function parseSettlementTicketSequence(?string $ticketNumber, ?string $periodKey = null): ?array
    {
        if ($ticketNumber === null || $ticketNumber === '') {
            return null;
        }

        if ($periodKey !== null) {
            $pattern = '/-'.preg_quote($periodKey, '/').'-(\d{6})$/';

            if (preg_match($pattern, $ticketNumber, $matches) !== 1) {
                return null;
            }

            return [$periodKey, (int) $matches[1]];
        }

        if (preg_match('/-(\d{4})-(\d{6})$/', $ticketNumber, $matches) !== 1) {
            return null;
        }

        return [$matches[1], (int) $matches[2]];
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
