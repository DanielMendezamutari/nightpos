<?php

use App\Application\StaffSettlement\Services\PendingCashSessionSettlementReconciler;
use App\Infrastructure\Persistence\Eloquent\Models\CashMovementModel;
use App\Infrastructure\Persistence\Eloquent\Models\ShowModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementItemModel;
use App\Infrastructure\Persistence\Eloquent\Models\StaffSettlementModel;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('nightpos:settlements:reconcile-pending-cash-session {--tenant-id=} {--branch-id=} {--cash-session-id=} {--apply}', function () {
    $tenantId = $this->option('tenant-id');
    $branchId = $this->option('branch-id');
    $cashSessionId = $this->option('cash-session-id');
    $apply = (bool) $this->option('apply');

    /** @var PendingCashSessionSettlementReconciler $reconciler */
    $reconciler = app(PendingCashSessionSettlementReconciler::class);

    $result = $reconciler->reconcile(
        tenantId: $tenantId !== null ? (int) $tenantId : null,
        branchId: $branchId !== null ? (int) $branchId : null,
        cashSessionId: $cashSessionId !== null ? (int) $cashSessionId : null,
        dryRun: ! $apply,
        actorUserId: null,
    );

    $mode = $apply ? 'APPLY' : 'DRY-RUN';
    $this->info("Reconciliacion settlements cash_session ($mode)");
    $this->table(
        ['grupos', 'cabeceras fusionadas', 'items movidos', 'ajustes movidos', 'items duplicados omitidos'],
        [[
            $result['groups'],
            $result['merged_headers'],
            $result['moved_items'],
            $result['moved_adjustments'],
            $result['skipped_duplicate_items'],
        ]],
    );
})->purpose('Consolida settlements PENDING por cash_session sin tocar PAID');

Artisan::command('nightpos:shows:audit-legacy-income {--tenant-id=} {--branch-id=} {--cash-session-id=} {--show-id=} {--apply}', function () {
    if ((bool) $this->option('apply')) {
        $this->error('Modo APPLY bloqueado intencionalmente. Primero resuelva manualmente la estrategia historica: algunos shows legacy no tienen item GIRL_SHOW ni egreso de settlement asociado.');

        return 1;
    }

    $tenantId = $this->option('tenant-id');
    $branchId = $this->option('branch-id');
    $cashSessionId = $this->option('cash-session-id');
    $showId = $this->option('show-id');

    $query = CashMovementModel::query()
        ->where('movement_type', 'INCOME')
        ->where(function ($inner) {
            $inner->where('source_type', 'SHOW')
                ->orWhere('movement_category', 'SHOW_COLLECTION');
        })
        ->orderBy('cash_session_id')
        ->orderBy('id');

    if ($tenantId !== null) {
        $query->where('tenant_id', (int) $tenantId);
    }

    if ($branchId !== null) {
        $query->where('branch_id', (int) $branchId);
    }

    if ($cashSessionId !== null) {
        $query->where('cash_session_id', (int) $cashSessionId);
    }

    if ($showId !== null) {
        $query->where('source_id', (int) $showId);
    }

    $movements = $query->get();

    $rows = [];
    $totalAmount = 0.0;

    foreach ($movements as $movement) {
        $show = null;

        if ((string) $movement->source_type === 'SHOW' && $movement->source_id !== null) {
            $show = ShowModel::query()->find((int) $movement->source_id);
        }

        $linkedItems = $movement->source_id !== null
            ? StaffSettlementItemModel::query()
                ->where('source_type', 'GIRL_SHOW')
                ->where('source_id', (int) $movement->source_id)
                ->get(['staff_settlement_id'])
            : collect();

        $linkedSettlements = $linkedItems->isEmpty()
            ? collect()
            : StaffSettlementModel::query()
                ->whereIn('id', $linkedItems->pluck('staff_settlement_id')->all())
                ->orderBy('id')
                ->get(['id', 'status', 'cash_session_id']);

        $rows[] = [
            'movement_id' => (int) $movement->id,
            'show_id' => $movement->source_id !== null ? (int) $movement->source_id : null,
            'cash_session_id' => (int) $movement->cash_session_id,
            'amount' => number_format((float) $movement->amount, 2, '.', ''),
            'girl' => $show?->girl?->name ?? 'N/D',
            'registered_at' => $show?->registered_at?->format('Y-m-d H:i:s') ?? $movement->created_at?->format('Y-m-d H:i:s'),
            'settlement_item' => $linkedItems->isNotEmpty() ? 'SI' : 'NO',
            'settlements' => $linkedSettlements->isEmpty()
                ? '—'
                : $linkedSettlements->map(fn ($settlement) => sprintf('%d:%s:caja%s', $settlement->id, $settlement->status, $settlement->cash_session_id ?? 'null'))->implode(', '),
            'movement_category' => (string) $movement->movement_category,
        ];

        $totalAmount += (float) $movement->amount;
    }

    $this->info('Auditoria de shows legacy clasificados como ingreso (DRY-RUN)');
    $this->table(
        ['movement_id', 'show_id', 'cash_session_id', 'amount', 'girl', 'registered_at', 'settlement_item', 'settlements', 'movement_category'],
        $rows,
    );
    $this->line('Candidatos: '.count($rows));
    $this->line('Monto total legacy detectado: '.number_format($totalAmount, 2, '.', ''));
    $this->warn('No se realizaron cambios. Use este reporte para definir la migracion historica por caja/show antes de cualquier reclasificacion.');

    return 0;
})->purpose('Detecta shows legacy mal clasificados como ingreso sin modificar datos');
