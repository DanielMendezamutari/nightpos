<?php

/**
 * Script temporal de depuración — NO modifica código de producción.
 * Ejecutar: php scripts/trace_mark_paid.php [settlement_id]
 * Usa ROLLBACK al final para no alterar datos (excepto si ya falla antes del commit).
 */

declare(strict_types=1);

use App\Application\DocumentSequence\Services\DocumentSequenceService;
use App\Application\StaffSettlement\Services\SettlementTicketNumberGenerator;
use App\Infrastructure\Persistence\Eloquent\Models\DocumentSequenceModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use App\Shared\Domain\Enums\DocumentSequenceType;
use Illuminate\Support\Facades\DB;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$settlementId = (int) ($argv[1] ?? 3);

function section(string $title): void
{
    echo "\n".str_repeat('=', 72)."\n{$title}\n".str_repeat('=', 72)."\n";
}

function dumpRow(?object $row): void
{
    if ($row === null) {
        echo "  (sin fila)\n";
        return;
    }
    foreach ((array) $row as $k => $v) {
        echo '  '.str_replace("\0", '', $k).' = '.var_export($v, true)."\n";
    }
}

section('1. SETTLEMENT RECIBIDO');
$settlement = StaffSettlementModel::query()->find($settlementId);
if ($settlement === null) {
    fwrite(STDERR, "Settlement {$settlementId} no encontrado\n");
    exit(1);
}
echo "  id          = {$settlement->id}\n";
echo "  tenant_id   = {$settlement->tenant_id}\n";
echo "  branch_id   = {$settlement->branch_id}\n";
echo "  status      = {$settlement->status}\n";
echo "  ticket_number = ".var_export($settlement->ticket_number, true)."\n";
echo "  net_amount  = {$settlement->net_amount}\n";

$tenantId = (int) $settlement->tenant_id;
$branchId = (int) $settlement->branch_id;
$periodKey = (string) now()->format('Y');
$documentType = DocumentSequenceType::SettlementPayment->value;

section('2. document_sequences ANTES de pedir secuencia');
$seqBefore = DB::select(
    'SELECT * FROM document_sequences WHERE tenant_id = ? AND branch_id = ? AND document_type = ? AND period_key = ?',
    [$tenantId, $branchId, $documentType, $periodKey],
);
echo '  filas encontradas: '.count($seqBefore)."\n";
foreach ($seqBefore as $i => $row) {
    echo "  --- fila {$i} ---\n";
    dumpRow($row);
}

section('3. DocumentSequenceService — traza manual (misma lógica que reserveNextWithLock)');
$criteria = [
    'tenant_id' => $tenantId,
    'branch_id' => $branchId,
    'document_type' => $documentType,
    'period_key' => $periodKey,
];

$trace = [
    'path' => null,
    'last_value_leido' => null,
    'last_value_calculado' => null,
    'last_value_guardado' => null,
    'valor_retornado' => null,
];

DB::beginTransaction();
try {
    $row = DocumentSequenceModel::query()->where($criteria)->lockForUpdate()->first();

    if ($row !== null) {
        $trace['path'] = 'increment_existing_row';
        $trace['last_value_leido'] = (int) $row->last_value;
        $trace['last_value_calculado'] = $trace['last_value_leido'] + 1;
        echo "  path                 = increment_existing_row\n";
        echo "  last_value leído     = {$trace['last_value_leido']}\n";
        echo "  last_value calculado = {$trace['last_value_calculado']}\n";

        $row->update(['last_value' => $trace['last_value_calculado']]);
        $row->refresh();
        $trace['last_value_guardado'] = (int) $row->last_value;
        $trace['valor_retornado'] = $trace['last_value_calculado'];
        echo "  last_value guardado  = {$trace['last_value_guardado']}\n";
        echo "  valor retornado      = {$trace['valor_retornado']}\n";
    } else {
        $trace['path'] = 'create_new_row';
        echo "  path                 = create_new_row (no existía fila)\n";
        echo "  last_value leído     = null\n";
        echo "  last_value calculado = 1\n";
        $created = DocumentSequenceModel::query()->create([...$criteria, 'last_value' => 1]);
        $trace['last_value_guardado'] = (int) $created->last_value;
        $trace['valor_retornado'] = 1;
        echo "  last_value guardado  = {$trace['last_value_guardado']}\n";
        echo "  valor retornado      = {$trace['valor_retornado']}\n";
    }

    section('4. SettlementTicketNumberGenerator');
    $sequence = $trace['valor_retornado'];
    $branch = \App\Infrastructure\Persistence\Eloquent\Models\BranchModel::query()->findOrFail($branchId);
    $prefix = strtoupper(trim((string) ($branch->code ?: ('B'.$branchId))));
    $year = (int) now()->format('Y');
    $ticket = sprintf('%s-%d-%06d', $prefix, $year, $sequence);
    echo "  branch.code          = {$branch->code}\n";
    echo "  sequence recibida    = {$sequence}\n";
    echo "  ticket generado      = {$ticket}\n";

    section('5. ANTES del UPDATE settlement — ticket_number a guardar');
    echo "  ticket_number        = {$ticket}\n";

    section('6. ¿Duplicado? — staff_settlements con ese ticket');
    $dupes = DB::select(
        'SELECT id, tenant_id, branch_id, status, ticket_number, paid_at FROM staff_settlements WHERE ticket_number = ?',
        [$ticket],
    );
    if ($dupes === []) {
        echo "  No hay filas con ticket_number='{$ticket}'\n";
        echo "  Escenario: NO duplicado previo → mark-paid debería continuar\n";
    } else {
        echo "  *** DUPLICADO ENCONTRADO ***\n";
        foreach ($dupes as $d) {
            echo "  id={$d->id} tenant={$d->tenant_id} branch={$d->branch_id} status={$d->status} ticket={$d->ticket_number} paid_at={$d->paid_at}\n";
        }
        if ($trace['valor_retornado'] === 1) {
            echo "  Escenario A: servicio devolvió 1\n";
        } else {
            echo "  Escenario B: servicio devolvió {$trace['valor_retornado']} pero ya existe otro registro\n";
        }
    }

    section('7. document_sequences DENTRO de transacción (antes ROLLBACK)');
    $seqMid = DB::select(
        'SELECT * FROM document_sequences WHERE tenant_id = ? AND branch_id = ? AND document_type = ? AND period_key = ?',
        [$tenantId, $branchId, $documentType, $periodKey],
    );
    foreach ($seqMid as $row) {
        dumpRow($row);
    }

    section('8. Simular UPDATE settlement (sin ejecutar) + verificar UNIQUE');
    $scopeDupes = DB::select(
        'SELECT id, tenant_id, branch_id, status, ticket_number FROM staff_settlements
         WHERE tenant_id = ? AND branch_id = ? AND ticket_number = ?',
        [$tenantId, $branchId, $ticket],
    );
    echo '  filas en scope (tenant+branch+ticket): '.count($scopeDupes)."\n";
    foreach ($scopeDupes as $d) {
        echo "  id={$d->id} status={$d->status} ticket={$d->ticket_number}\n";
    }
    if (count($scopeDupes) > 0 && $settlement->status === 'PENDING') {
        echo "  => EloquentStaffSettlementRepository::markPaid línea ~916 lanzaría UNIQUE violation\n";
        echo "  => MarkSettlementPaidUseCase catch QueryException línea ~292-302 → HTTP 409\n";
    }

    DB::rollBack();
    echo "\n[ROLLBACK ejecutado — document_sequences no persistido en esta simulación]\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo "\n[EXCEPCIÓN en simulación] ".$e->getMessage()."\n";
    echo $e->getFile().':'.$e->getLine()."\n";
}

section('9. document_sequences DESPUÉS de ROLLBACK (estado real DB)');
$seqAfter = DB::select(
    'SELECT * FROM document_sequences WHERE tenant_id = ? AND branch_id = ? AND document_type = ? AND period_key = ?',
    [$tenantId, $branchId, $documentType, $periodKey],
);
foreach ($seqAfter as $row) {
    dumpRow($row);
}

section('10. Todos los tickets pagados en este tenant/branch');
$paid = DB::select(
    'SELECT id, status, ticket_number, paid_at FROM staff_settlements
     WHERE tenant_id = ? AND branch_id = ? AND ticket_number IS NOT NULL ORDER BY id',
    [$tenantId, $branchId],
);
foreach ($paid as $p) {
    echo "  id={$p->id} status={$p->status} ticket={$p->ticket_number} paid_at={$p->paid_at}\n";
}

section('11. Ejecutar reserveNext REAL via servicio (en transacción + ROLLBACK)');
DB::beginTransaction();
try {
    $before = app(DocumentSequenceService::class)->currentValue($tenantId, $branchId, DocumentSequenceType::SettlementPayment, $periodKey);
    echo "  currentValue ANTES = ".var_export($before, true)."\n";
    $returned = app(DocumentSequenceService::class)->reserveNext($tenantId, $branchId, DocumentSequenceType::SettlementPayment, $periodKey);
    echo "  reserveNext() retornó = {$returned}\n";
    $after = app(DocumentSequenceService::class)->currentValue($tenantId, $branchId, DocumentSequenceType::SettlementPayment, $periodKey);
    echo "  currentValue DENTRO TX = ".var_export($after, true)."\n";
    $ticketViaService = app(SettlementTicketNumberGenerator::class)->next($tenantId, $branchId);
    echo "  generator->next() (2da llamada en misma TX!) = {$ticketViaService}\n";
    DB::rollBack();
    echo "  [ROLLBACK]\n";
} catch (Throwable $e) {
    DB::rollBack();
    echo '  EXCEPCIÓN: '.$e->getMessage()."\n";
}
$afterRollback = app(DocumentSequenceService::class)->currentValue($tenantId, $branchId, DocumentSequenceType::SettlementPayment, $periodKey);
echo "  currentValue DESPUÉS ROLLBACK = ".var_export($afterRollback, true)."\n";

echo "\nDone.\n";
