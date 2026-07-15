<?php

declare(strict_types=1);

namespace App\Application\Printing\Services;

use App\Shared\Domain\Enums\PrintJobType;

final class PrintTicketContentBuilder
{
    private const SALE_MODE_LABELS = [
        'SOLO_CLIENTE' => 'Solo',
        'CON_ACOMPANANTE' => 'Acomp',
        'MIXED' => 'Mixto',
    ];

    /**
     * @param  array<string, mixed>  $order  Order from OrderPresentationService
     */
    public function buildOrderCommand(
        array $order,
        ?string $waiterName,
        ?string $serviceAreaName,
        int $paperWidthMm = 80,
        bool $isReprint = false,
        ?int $correctionNumber = null,
        ?string $printedAt = null,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];
        $orderNumber = (string) ($order['order_number'] ?? '—');

        if ($isReprint) {
            $lines[] = $this->center('REIMPRESION', $width);
            if ($correctionNumber !== null && $correctionNumber > 0) {
                $lines[] = $this->center('COMANDA #'.$orderNumber.'-'.$correctionNumber, $width);
                $lines[] = $this->center('Correccion #'.$correctionNumber, $width);
            } else {
                $lines[] = $this->center('COMANDA #'.$orderNumber, $width);
            }
        } else {
            $lines[] = $this->center('COMANDA #'.$orderNumber, $width);
        }

        if ($serviceAreaName !== null && $serviceAreaName !== '') {
            $lines[] = $this->center(strtoupper($serviceAreaName), $width);
        }

        $lines[] = str_repeat('=', $width);

        $tableLabel = (string) ($order['table_label'] ?? '');
        if ($tableLabel !== '') {
            $locationLabel = $this->resolveLocationLabel($tableLabel);
            $lines[] = $this->center($locationLabel.': '.$tableLabel, $width);
        }

        if ($waiterName !== null && $waiterName !== '') {
            $lines[] = $this->row('Garzon', $waiterName, $width);
        }

        $createdAt = $order['opened_at'] ?? null;
        if ($createdAt !== null) {
            $lines[] = $this->row('Creada', $this->formatTime((string) $createdAt), $width);
        }

        $printedTimestamp = $printedAt ?? $order['sent_to_bar_at'] ?? now()->toIso8601String();
        $lines[] = $this->row('Impresa', $this->formatTime((string) $printedTimestamp), $width);
        $lines[] = $this->row('Estado', 'EN BARRA', $width);

        $lines[] = str_repeat('-', $width);

        foreach ($this->visibleItems($order) as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $name = (string) ($item['product_name'] ?? 'Producto');
            $mode = self::SALE_MODE_LABELS[$item['sale_mode'] ?? ''] ?? ($item['sale_mode'] ?? '');
            $lines[] = sprintf('%dx %s', $qty, $this->truncate($name, $width - 4));

            if ($mode !== '') {
                $lines[] = '   '.$mode;
            }

            if (($item['sale_mode'] ?? '') === 'CON_ACOMPANANTE' && ! ($item['requires_allocation'] ?? false)) {
                $girlName = $item['girl_name'] ?? null;
                if ($girlName) {
                    $lines[] = '   Chica: '.$girlName;
                }
            }

            if ($item['requires_allocation'] ?? false) {
                $allocated = (int) ($item['allocated_bracelet_units'] ?? 0);
                $required = (int) ($item['required_bracelet_units'] ?? 0);
                $lines[] = "   Manillas: {$allocated}/{$required}";

                foreach ($item['allocations'] ?? [] as $alloc) {
                    $girl = (string) ($alloc['girl_name'] ?? '—');
                    $units = (int) ($alloc['units'] ?? 0);
                    $lines[] = "   {$girl} x{$units}";
                }
            }

            if (! empty($item['notes'])) {
                $lines[] = '   Nota: '.$this->truncate((string) $item['notes'], $width - 9);
            }
        }

        if (! empty($order['notes'])) {
            $lines[] = str_repeat('-', $width);
            $lines[] = 'Obs: '.$this->truncate((string) $order['notes'], $width - 5);
        }

        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $order  Order from OrderPresentationService
     */
    public function buildPrecheck(
        array $order,
        ?string $branchName,
        ?string $waiterName,
        ?string $serviceAreaName,
        int $paperWidthMm = 80,
        ?string $printedAt = null,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];
        $orderNumber = (string) ($order['order_number'] ?? '—');

        $lines[] = $this->center($branchName ?: 'NIGHTPOS', $width);
        $lines[] = $this->center('PRECUENTA #'.$orderNumber, $width);
        $lines[] = str_repeat('=', $width);
        $lines[] = $this->row('Estado', 'PENDIENTE DE COBRO', $width);

        $tableLabel = (string) ($order['table_label'] ?? '');
        if ($tableLabel !== '') {
            $lines[] = $this->center($this->resolveLocationLabel($tableLabel).': '.$tableLabel, $width);
        }

        if ($serviceAreaName !== null && $serviceAreaName !== '') {
            $lines[] = $this->row('Salon', $serviceAreaName, $width);
        }

        if ($waiterName !== null && $waiterName !== '') {
            $lines[] = $this->row('Garzon', $waiterName, $width);
        }

        $createdAt = $order['opened_at'] ?? now()->toIso8601String();
        $lines[] = $this->row('Creada', $this->formatTime((string) $createdAt), $width);
        $lines[] = $this->row('Impresa', $this->formatTime((string) ($printedAt ?? now()->toIso8601String())), $width);

        $lines[] = str_repeat('-', $width);

        foreach ($this->visibleItems($order) as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            $name = (string) ($item['product_name'] ?? 'Producto');
            $mode = self::SALE_MODE_LABELS[$item['sale_mode'] ?? ''] ?? ($item['sale_mode'] ?? '');
            $lines[] = sprintf('%dx %s', $qty, $this->truncate($name, $width - 4));

            if ($mode !== '') {
                $lines[] = '   '.$mode;
            }

            if (($item['sale_mode'] ?? '') === 'CON_ACOMPANANTE' && ! ($item['requires_allocation'] ?? false)) {
                $girlName = $item['girl_name'] ?? null;
                if ($girlName) {
                    $lines[] = '   Manilla: '.$girlName;
                }
            }

            if ($item['requires_allocation'] ?? false) {
                $allocated = (int) ($item['allocated_bracelet_units'] ?? 0);
                $required = (int) ($item['required_bracelet_units'] ?? 0);
                $lines[] = "   Manillas: {$allocated}/{$required}";

                foreach ($item['allocations'] ?? [] as $alloc) {
                    $girl = (string) ($alloc['girl_name'] ?? '—');
                    $units = (int) ($alloc['units'] ?? 0);
                    $lines[] = "   {$girl} x{$units}";
                }
            }
        }

        $lines[] = str_repeat('-', $width);

        $total = $order['total'] ?? '0.00';
        $currency = $order['currency'] ?? 'BOB';
        $lines[] = $this->center('TOTAL', $width);
        $lines[] = $this->center("{$total} {$currency}", $width);

        $lines[] = str_repeat('=', $width);
        foreach ($this->wrap('Gracias por su preferencia.', $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        foreach ($this->wrap('No tiene validez fiscal.', $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $sale
     * @param  array<string, mixed>|null  $order
     */
    public function buildSaleReceipt(
        array $sale,
        ?array $order,
        ?string $cashierName,
        ?string $waiterName,
        ?string $serviceAreaName,
        ?string $branchName,
        int $paperWidthMm = 80,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];

        $operationNumber = (string) ($order['order_number'] ?? $sale['sale_number'] ?? '—');
        $lines[] = $this->center($branchName ?: 'NIGHTPOS', $width);
        $lines[] = $this->center('PAGO #'.$operationNumber, $width);
        $lines[] = str_repeat('=', $width);

        $lines[] = $this->row('Estado', 'PAGADO', $width);
        $lines[] = $this->row('Metodo', $this->paymentModeLabel((string) ($sale['payment_mode'] ?? '')), $width);

        $tableLabel = (string) ($order['table_label'] ?? $sale['table_label'] ?? '');
        if ($tableLabel !== '') {
            $lines[] = $this->center($this->resolveLocationLabel($tableLabel).': '.$tableLabel, $width);
        }

        if ($serviceAreaName !== null && $serviceAreaName !== '') {
            $lines[] = $this->row('Salon', $serviceAreaName, $width);
        }

        if ($waiterName !== null && $waiterName !== '') {
            $lines[] = $this->row('Garzon', $waiterName, $width);
        }

        if ($cashierName !== null && $cashierName !== '') {
            $lines[] = $this->row('Cajera', $cashierName, $width);
        }

        $paidAt = (string) ($sale['paid_at'] ?? now()->toIso8601String());
        $lines[] = $this->row('Cobro', $this->formatTime($paidAt), $width);

        $payments = $sale['payments'] ?? [];
        if (count($payments) > 1 || strtoupper((string) ($sale['payment_mode'] ?? '')) === 'MIXED') {
            $lines[] = str_repeat('-', $width);
            foreach ($payments as $payment) {
                $method = $this->paymentModeLabel((string) ($payment['payment_method'] ?? ''));
                $amount = (string) ($payment['amount'] ?? '0.00');
                $lines[] = $this->row($method, $amount, $width);
            }
        }

        $lines[] = str_repeat('-', $width);

        $total = $sale['total'] ?? '0.00';
        $currency = $sale['currency'] ?? 'BOB';
        $lines[] = $this->center('TOTAL', $width);
        $lines[] = $this->center("{$total} {$currency}", $width);

        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $roomService
     */
    public function buildRoomService(
        array $roomService,
        ?string $branchName,
        int $paperWidthMm = 80,
        ?string $printedAt = null,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];

        $lines[] = $this->center($branchName ?: 'NIGHTPOS', $width);
        $lines[] = $this->center('PIEZA', $width);
        $lines[] = str_repeat('=', $width);

        $roomLabel = (string) ($roomService['room_label'] ?? $roomService['room_number'] ?? '—');
        $lines[] = $this->center('Pieza: '.$roomLabel, $width);

        $girlName = (string) ($roomService['girl_name'] ?? '—');
        $lines[] = $this->row('Chica', $girlName, $width);

        $startedAt = $roomService['started_at'] ?? $roomService['registered_at'] ?? null;
        if ($startedAt !== null) {
            $lines[] = $this->row('Inicio', $this->formatTime((string) $startedAt), $width);
        }

        $duration = (int) ($roomService['duration_minutes'] ?? 0);
        if ($duration > 0) {
            $lines[] = $this->row('Duracion', "{$duration} min", $width);
        }

        $lines[] = str_repeat('-', $width);

        $total = (string) ($roomService['total_amount'] ?? '0.00');
        $currency = 'BOB';
        $girlPercent = (string) ($roomService['girl_percent'] ?? '0');
        $grossGirl = (string) ($roomService['gross_girl_amount'] ?? $roomService['girl_amount'] ?? '0.00');
        $girlNet = (string) ($roomService['girl_amount'] ?? '0.00');
        $house = (string) ($roomService['house_amount'] ?? '0.00');
        $cleaning = (string) ($roomService['cleaning_amount'] ?? '0.00');

        $lines[] = $this->row('Total', "{$total} {$currency}", $width);
        $lines[] = $this->row("Chica {$girlPercent}%", "{$grossGirl} {$currency}", $width);

        if ((float) $cleaning > 0) {
            $lines[] = $this->row('Limpieza', "-{$cleaning} {$currency}", $width);
            $lines[] = $this->row('Chica neta', "{$girlNet} {$currency}", $width);
        }

        $lines[] = $this->row('Casa', "{$house} {$currency}", $width);

        $lines[] = str_repeat('-', $width);

        $status = strtoupper((string) ($roomService['status'] ?? 'ACTIVE'));
        $statusLabel = match ($status) {
            'ACTIVE' => 'ACTIVA',
            'DUE' => 'TIEMPO CUMPLIDO',
            'FINISHED' => 'FINALIZADA',
            'CANCELLED' => 'CANCELADA',
            default => $status,
        };
        $lines[] = $this->row('Estado', $statusLabel, $width);

        $registeredBy = (string) ($roomService['registered_by_name'] ?? '—');
        $lines[] = $this->row('Registro', $registeredBy, $width);

        $lines[] = $this->row('Impresa', $this->formatTime((string) ($printedAt ?? now()->toIso8601String())), $width);

        if (! empty($roomService['notes'])) {
            $lines[] = str_repeat('-', $width);
            $lines[] = 'Obs: '.$this->truncate((string) $roomService['notes'], $width - 5);
        }

        $lines[] = str_repeat('=', $width);
        foreach ($this->wrap('Comanda operativa — no fiscal.', $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $show
     */
    public function buildShowTicket(
        array $show,
        ?string $branchName,
        int $paperWidthMm = 80,
        ?string $printedAt = null,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];

        $lines[] = $this->center($branchName ?: 'NIGHTPOS', $width);
        $lines[] = $this->center('SHOW', $width);
        $lines[] = str_repeat('=', $width);

        $showType = (string) ($show['show_type_label'] ?? $show['show_type'] ?? '—');
        $lines[] = $this->row('Tipo', $showType, $width);

        $girlName = (string) ($show['girl_name'] ?? '—');
        $lines[] = $this->row('Chica', $girlName, $width);

        $registeredAt = $show['registered_at'] ?? null;
        if ($registeredAt !== null) {
            $lines[] = $this->row('Hora', $this->formatTime((string) $registeredAt), $width);
        }

        $lines[] = str_repeat('-', $width);

        $total = (string) ($show['total_amount'] ?? '0.00');
        $currency = 'BOB';
        $lines[] = $this->row('Total', "{$total} {$currency}", $width);
        $lines[] = $this->row('Chica', "{$total} {$currency}", $width);
        $lines[] = $this->row('Casa', "0.00 {$currency}", $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->row('Estado', 'REGISTRADO', $width);

        $registeredBy = (string) ($show['registered_by_name'] ?? '—');
        $lines[] = $this->row('Registro', $registeredBy, $width);

        $lines[] = $this->row('Impresa', $this->formatTime((string) ($printedAt ?? now()->toIso8601String())), $width);

        if (! empty($show['notes'])) {
            $lines[] = str_repeat('-', $width);
            $lines[] = 'Obs: '.$this->truncate((string) $show['notes'], $width - 5);
        }

        $lines[] = str_repeat('=', $width);
        foreach ($this->wrap('Comanda operativa — no fiscal.', $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function buildSettlementPayment(
        array $payload,
        int $paperWidthMm = 80,
        ?string $printedAt = null,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $settlement = $payload['settlement'] ?? [];
        $lines = [];

        if (! empty($payload['is_reprint'])) {
            $lines[] = $this->center('REIMPRESION', $width);
            $reprintNumber = (int) ($payload['reprint_number'] ?? 0);
            if ($reprintNumber > 0) {
                $lines[] = $this->center('N° '.$reprintNumber, $width);
            }
            if (! empty($payload['reprinted_at'])) {
                $lines[] = $this->center($this->formatDateTime((string) $payload['reprinted_at']), $width);
            }
            if (! empty($payload['reprinted_by_name'])) {
                $lines[] = $this->center((string) $payload['reprinted_by_name'], $width);
            }
            $lines[] = str_repeat('-', $width);
        }

        $lines[] = $this->center('LIQUIDACION PAGADA', $width);
        $lines[] = str_repeat('=', $width);

        $roleLabel = match ((string) ($settlement['staff_role'] ?? '')) {
            'GIRL' => 'Chica',
            'WAITER' => 'Garzon',
            'CLEANING' => 'Limpieza',
            default => (string) ($settlement['staff_role'] ?? '—'),
        };

        $lines[] = $this->row('Persona', $this->truncate((string) ($settlement['staff_name'] ?? '—'), $width - 10), $width);
        $lines[] = $this->row('Rol', $roleLabel, $width);
        $lines[] = $this->row('Caja', '#'.(string) ($settlement['cash_session_id'] ?? '—'), $width);

        $shiftLabel = trim(implode(' · ', array_filter([
            $payload['shift_name'] ?? null,
            $payload['shift_business_date'] ?? null,
        ])));

        if ($shiftLabel !== '') {
            $lines[] = $this->row('Turno', $this->truncate($shiftLabel, $width - 8), $width);
        }

        if (! empty($settlement['cut_label'])) {
            $lines[] = $this->row('Corte', (string) $settlement['cut_label'], $width);
        }

        if (! empty($settlement['ticket_number'])) {
            $lines[] = $this->row('Ticket', (string) $settlement['ticket_number'], $width);
        }

        $lines[] = str_repeat('-', $width);

        $isWaiter = ($settlement['settlement_type'] ?? '') === 'WAITER'
            || ($settlement['staff_role'] ?? '') === 'WAITER';

        $waiterSnapshot = $settlement['waiter_snapshot'] ?? null;

        if ($isWaiter && is_array($waiterSnapshot)) {
            $lines[] = $this->center('VENTA GARZON', $width);
            $lines[] = str_repeat('-', $width);
            $lines[] = $this->row('Venta total', ((string) ($waiterSnapshot['sales_total'] ?? '0.00')).' Bs', $width);

            if (! empty($waiterSnapshot['commission_percent'])) {
                $lines[] = $this->row('Porcentaje', rtrim(rtrim((string) $waiterSnapshot['commission_percent'], '0'), '.').'%', $width);
            }

            $lines[] = $this->row('Comision', ((string) ($waiterSnapshot['commission_amount'] ?? '0.00')).' Bs', $width);
        }
        else {
            $lines[] = $this->row('BRUTO', ((string) ($settlement['gross_amount'] ?? '0.00')).' Bs', $width);
        }

        $cleaning = (float) ($settlement['cleaning_amount'] ?? 0);
        if ($cleaning !== 0.0) {
            $lines[] = $this->row('Limpieza', number_format($cleaning, 2, '.', '').' Bs', $width);
        }

        $manualDiscount = (float) ($settlement['manual_discount_amount'] ?? 0);
        if ($manualDiscount !== 0.0) {
            $lines[] = $this->row('Descuento', number_format($manualDiscount, 2, '.', '').' Bs', $width);
        }

        $finesTotal = 0.0;
        foreach ($settlement['fines'] ?? [] as $fine) {
            $finesTotal += (float) ($fine['amount'] ?? 0);
        }

        if ($finesTotal !== 0.0) {
            if ($isWaiter) {
                $lines[] = $this->row('Multas', '-'.number_format($finesTotal, 2, '.', '').' Bs', $width);
            }
            else {
                foreach ($settlement['fines'] ?? [] as $fine) {
                    $label = $this->truncate((string) ($fine['reason'] ?? 'Multa'), $width - 10);
                    $lines[] = $this->row('Multa', $label, $width);
                    $lines[] = $this->row('', number_format((float) ($fine['amount'] ?? 0), 2, '.', '').' Bs', $width);
                }
            }
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->row('NETO PAGADO', ((string) ($settlement['net_amount'] ?? '0.00')).' Bs', $width);
        $lines[] = str_repeat('-', $width);

        $lines[] = $this->row('Metodo', $this->paymentModeLabel((string) ($settlement['payment_method'] ?? 'CASH')), $width);
        $lines[] = $this->row('Pagado por', $this->truncate((string) ($settlement['paid_by_name'] ?? '—'), $width - 12), $width);

        $paidAt = (string) ($settlement['paid_at'] ?? $printedAt ?? now()->toIso8601String());
        $lines[] = $this->row('Fecha', $this->formatDateTime($paidAt), $width);
        $lines[] = $this->row('Hora', $this->formatTime($paidAt), $width);

        if (! empty($settlement['notes'])) {
            $lines[] = str_repeat('-', $width);
            $lines[] = 'Obs: '.$this->truncate((string) $settlement['notes'], $width - 5);
        }

        $lines[] = str_repeat('=', $width);
        $lines[] = $this->center('Powered by Ribersoft', $width);
        $lines[] = $this->center('WhatsApp 67369293', $width);
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $movement
     */
    public function buildCashMovement(
        array $movement,
        ?string $branchName,
        ?string $cashierName,
        int $paperWidthMm = 80,
        ?string $printedAt = null,
    ): string {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $lines = [];

        $lines[] = $this->center($branchName ?: 'NIGHTPOS', $width);
        $lines[] = $this->center('MOVIMIENTO DE CAJA', $width);
        $lines[] = str_repeat('=', $width);

        $type = strtoupper((string) ($movement['movement_type'] ?? ''));
        $typeLabel = $type === 'INCOME' ? 'Ingreso' : ($type === 'EXPENSE' ? 'Egreso' : $type);
        $lines[] = $this->row('Tipo', $typeLabel, $width);
        $lines[] = $this->row('Metodo', $this->paymentModeLabel((string) ($movement['payment_method'] ?? 'CASH')), $width);
        $lines[] = $this->row('Monto', ((string) ($movement['amount'] ?? '0.00')).' BOB', $width);

        $reason = (string) ($movement['reason_name'] ?? $movement['description'] ?? '—');
        $lines[] = $this->row('Motivo', $this->truncate($reason, $width - 8), $width);

        if (! empty($movement['notes'])) {
            $lines[] = $this->row('Detalle', $this->truncate((string) $movement['notes'], $width - 8), $width);
        } elseif (! empty($movement['description']) && $movement['description'] !== $reason) {
            $lines[] = $this->row('Detalle', $this->truncate((string) $movement['description'], $width - 8), $width);
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->row('Cajera', $cashierName ?: '—', $width);
        $lines[] = $this->row('Caja', '#'.(string) ($movement['cash_session_id'] ?? '—'), $width);
        $lines[] = $this->row('Fecha', $this->formatDateTime((string) ($movement['created_at'] ?? $printedAt ?? now()->toIso8601String())), $width);
        if ($branchName) {
            $lines[] = $this->row('Sucursal', $this->truncate($branchName, $width - 10), $width);
        }
        $lines[] = $this->row('Estado', 'REGISTRADO', $width);
        $lines[] = $this->row('Impresa', $this->formatTime((string) ($printedAt ?? now()->toIso8601String())), $width);

        $lines[] = str_repeat('=', $width);
        foreach ($this->wrap('Comprobante operativo — no fiscal.', $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function buildCashClose(array $payload, int $paperWidthMm = 80, ?string $printedAt = null): string
    {
        return $this->buildCashCloseSummaryTicket($payload, $paperWidthMm, $printedAt);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function buildCashCloseSummaryTicket(array $payload, int $paperWidthMm = 80, ?string $printedAt = null): string
    {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $session = $payload['session'] ?? [];
        $dashboard = $payload['financial_dashboard'] ?? [];
        $salesSummary = $dashboard['sales_summary'] ?? [];
        $cashSummary = $dashboard['cash_summary'] ?? [];
        $settlementSummary = $dashboard['settlement_summary'] ?? [];
        $scopeSummary = $dashboard['scope_summary'] ?? ($payload['scope_summary'] ?? []);
        $productsSold = $payload['products_sold'] ?? $payload['top_products'] ?? [];
        $lines = [];

        $branchName = (string) ($payload['branch_name'] ?? 'NIGHTPOS');
        $lines[] = $this->center($branchName, $width);
        $lines[] = $this->center('REPORTE DE VENTAS / CIERRE DE CAJA', $width);
        $lines[] = str_repeat('=', $width);

        $lines[] = $this->center('ENCABEZADO', $width);
        $lines[] = $this->cashCloseKeyValueLine('Sucursal', $branchName, $width);
        $lines[] = $this->cashCloseKeyValueLine('Caja', '#'.(string) ($session['id'] ?? '—'), $width);
        $lines[] = $this->cashCloseKeyValueLine('Cajera', (string) ($payload['cashier_name'] ?? '—'), $width);
        $lines[] = $this->cashCloseKeyValueLine('Turno', (string) ($payload['current_shift_label'] ?? $payload['shift_label'] ?? $scopeSummary['scope_label'] ?? '—'), $width);
        $lines[] = $this->cashCloseKeyValueLine('Apertura', $this->formatDateTimeLong((string) ($session['opened_at'] ?? '')), $width);
        $lines[] = $this->cashCloseKeyValueLine('Cierre', $this->formatDateTimeLong((string) ($session['closed_at'] ?? '')), $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('MONTO INICIAL', $width);
        $lines[] = $this->cashCloseAmountLine('Monto inicial Bs', (string) ($cashSummary['opening_cash'] ?? '0.00'), $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('COBROS / VENTAS', $width);
        $lines[] = $this->cashCloseAmountLine('Venta total', (string) ($salesSummary['total_sales_amount'] ?? $salesSummary['total_sales'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Efectivo', (string) ($salesSummary['cash_total'] ?? $salesSummary['sales_cash'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('QR', (string) ($salesSummary['qr_total'] ?? $salesSummary['sales_qr'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Tarjeta', (string) ($salesSummary['card_total'] ?? $salesSummary['sales_card'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseCompactLine('Mixto', (string) ($salesSummary['mixed_sales_count'] ?? 0), $width);
        $lines[] = $this->cashCloseCompactLine('Cantidad de ventas', (string) ($salesSummary['sales_count'] ?? 0), $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('VENTAS POR ORIGEN', $width);
        $lines[] = $this->cashCloseAmountLine('Comandas cobradas', (string) ($salesSummary['by_source']['order_sales'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Venta directa', (string) ($salesSummary['by_source']['direct_sales'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Piezas', (string) ($salesSummary['by_source']['room_services'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Manillas', (string) ($salesSummary['by_source']['bracelets'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Otros ingresos comerciales', (string) ($salesSummary['by_source']['other_sales'] ?? '0.00'), $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('CAJA FISICA', $width);
        $lines[] = $this->cashCloseAmountLine('Ingresos manuales', (string) ($cashSummary['cash_income_manual'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Egresos CASH no personal', number_format(
            (float) ($cashSummary['cash_expense_operational'] ?? 0)
            + (float) ($cashSummary['cash_expense_purchases'] ?? 0)
            + (float) ($cashSummary['cash_expense_other'] ?? 0),
            2,
            '.',
            '',
        ), $width);
        $lines[] = $this->cashCloseAmountLine('Liquidaciones pagadas', (string) ($cashSummary['cash_expense_settlements'] ?? '0.00'), $width);

        $lines[] = $this->cashCloseAmountLine('Efectivo esperado', (string) ($cashSummary['expected_cash'] ?? '0.00'), $width);
        $lines[] = $this->cashCloseAmountLine('Efectivo contado', (string) ($cashSummary['counted_cash'] ?? '0.00'), $width);
        $differenceAmount = (string) ($cashSummary['cash_difference'] ?? '0.00');
        $lines[] = str_repeat('*', $width);
        $lines[] = $this->cashCloseAmountLine('DIFERENCIA', $differenceAmount, $width, false, true);
        $lines[] = str_repeat('*', $width);

        $productRows = [];
        $sortedProducts = array_values(array_filter(
            $productsSold,
            static fn (array $row): bool => (int) ($row['quantity_sold'] ?? 0) > 0,
        ));
        usort($sortedProducts, static function (array $left, array $right): int {
            $qtyCompare = (int) ($right['quantity_sold'] ?? 0) <=> (int) ($left['quantity_sold'] ?? 0);
            if ($qtyCompare !== 0) {
                return $qtyCompare;
            }

            $amountCompare = (float) ($right['total_amount'] ?? 0) <=> (float) ($left['total_amount'] ?? 0);
            if ($amountCompare !== 0) {
                return $amountCompare;
            }

            return strcmp((string) ($left['product_name'] ?? ''), (string) ($right['product_name'] ?? ''));
        });

        foreach ($sortedProducts as $row) {
            $quantity = (int) ($row['quantity_sold'] ?? 0);
            $productRows[] = $this->cashCloseProductLine(
                $quantity,
                (string) ($row['product_name'] ?? 'Producto'),
                $width,
            );
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('VENTAS TOTALES / PRODUCTOS', $width);
        $lines[] = $this->cashCloseProductHeader($width);
        foreach ($productRows as $productLine) {
            $lines[] = $productLine;
        }
        $totalUnits = (int) ($payload['products_sold_total_units'] ?? array_sum(array_map(
            static fn (array $row): int => (int) ($row['quantity_sold'] ?? 0),
            $sortedProducts,
        )));
        $lines[] = str_repeat('-', $width);
        $lines[] = $this->cashCloseCompactLine('TOTAL ITEMS VENDIDOS', (string) $totalUnits, $width);
        $lines[] = $this->cashCloseAmountLine('OTROS PRODUCTOS', '0 unidades', $width, false);

        $pendingGirls = (string) ($settlementSummary['girls']['pending_net_amount'] ?? '0.00');
        $pendingWaiters = (string) ($settlementSummary['waiters']['pending_net_amount'] ?? '0.00');
        $pendingCleaning = (string) ($settlementSummary['cleaning']['pending_net_amount'] ?? '0.00');
        $pendingTotal = (string) ($settlementSummary['totals']['pending_total_net'] ?? '0.00');
        $hasPending = ((float) $pendingGirls !== 0.0) || ((float) $pendingWaiters !== 0.0) || ((float) $pendingCleaning !== 0.0);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('PENDIENTES', $width);
        if ($hasPending) {
            $lines[] = str_repeat('*', $width);
            $lines[] = $this->cashCloseAmountLine('Chicas', $pendingGirls, $width, false, true);
            $lines[] = $this->cashCloseAmountLine('Garzones', $pendingWaiters, $width, false, true);
            $lines[] = $this->cashCloseAmountLine('Limpieza', $pendingCleaning, $width, false, true);
            $lines[] = $this->cashCloseAmountLine('TOTAL PENDIENTE', $pendingTotal, $width, false, true);
            $lines[] = str_repeat('*', $width);
        } else {
            $lines[] = $this->cashCloseCompactLine('Chicas', '0', $width);
            $lines[] = $this->cashCloseCompactLine('Garzones', '0', $width);
            $lines[] = $this->cashCloseCompactLine('Limpieza', '0', $width);
            $lines[] = $this->cashCloseCompactLine('Total pendiente', '0', $width);
        }

        $personnelRows = $settlementSummary['totals']['personnel_rows'] ?? [];
        $waiterPersonnel = $personnelRows['waiters'] ?? [];
        $girlPersonnel = $personnelRows['girls'] ?? [];
        $cleaningPersonnel = $personnelRows['cleaning'] ?? [];

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('OBSERVACIONES', $width);
        $lines[] = str_repeat('_', $width);
        $lines[] = str_repeat('_', $width);
        $lines[] = str_repeat('_', $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('FIRMA CAJERA', $width);
        $lines[] = str_repeat('_', $width);

        $lines[] = str_repeat('-', $width);
        $lines[] = 'Impreso: '.$this->formatDateTimeLong((string) ($printedAt ?? now()->toIso8601String()));
        $footer = (string) config('nightpos.printing.ticket_footer', 'Powered by Ribersoft - WhatsApp 67369293');
        foreach ($this->wrap($footer, $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        $lines[] = str_repeat('=', $width);

        return implode("\n", array_map(fn (string $line): string => $this->thermalSafe($line), $lines))."\n";
    }

    /**
     * Normaliza texto para impresoras termicas (CP437/ASCII).
     */
    private function thermalSafe(string $line): string
    {
        static $search = ["\u{2014}", "\u{2013}", "\u{00B7}", "\u{2026}", "\u{2022}", "\u{00BA}", "\u{2018}", "\u{2019}", "\u{201C}", "\u{201D}", "\u{00A0}"];
        static $replace = ['-', '-', '-', '...', '-', '', "'", "'", '"', '"', ' '];

        return str_replace($search, $replace, $line);
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function cashCloseBannerLines(array $payload, int $width): array
    {
        if ($payload['is_forced_close'] ?? false) {
            return [$this->center('*** CIERRE ADMINISTRATIVO ***', $width)];
        }

        $session = $payload['session'] ?? [];
        $summary = $payload['summary'] ?? [];
        $difference = abs((float) ($summary['cash_difference'] ?? $session['difference_amount'] ?? 0));
        $hasNotes = ! empty($session['closing_notes']) || ! empty($session['opening_notes']);
        $hasBlockers = ($payload['blocker_messages'] ?? []) !== [];
        $hasMismatch = (int) ($payload['reconciliation_mismatch_count'] ?? 0) > 0;

        if ($hasNotes || $hasBlockers || $difference > 0.009 || $hasMismatch) {
            return [$this->center('** CIERRE CON OBSERVACIONES **', $width)];
        }

        return [$this->center('CIERRE NORMAL', $width)];
    }

    /**
     * @param  list<array{0: string, 1: string}>  $rows
     * @return list<string>
     */
    private function sectionLines(string $title, int $width, array $rows): array
    {
        $lines = [str_repeat('-', $width), $this->center($title, $width)];

        foreach ($rows as [$label, $value]) {
            if ($label === '' && $value !== '') {
                foreach ($this->wrap($value, $width) as $wrapped) {
                    $lines[] = $wrapped;
                }
                continue;
            }

            $lines[] = $this->row($label, $value, $width);
        }

        return $lines;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function cashCloseIncidents(array $payload, string $difference): array
    {
        $items = [];

        if (abs((float) $difference) > 0.009) {
            $items[] = 'Diferencia arqueo: '.$difference.' BOB';
        }

        foreach ($payload['blocker_messages'] ?? [] as $message) {
            if ($message !== '') {
                $items[] = (string) $message;
            }
        }

        foreach ($payload['reconciliation_issues'] ?? [] as $issue) {
            if ($issue !== '') {
                $items[] = (string) $issue;
            }
        }

        if ($payload['is_forced_close'] ?? false) {
            $forced = $payload['forced_close'] ?? [];
            $reason = (string) ($forced['forced_close_reason_label'] ?? $forced['forced_close_reason'] ?? '');
            if ($reason !== '') {
                $items[] = 'Motivo admin: '.$reason;
            }
        }

        return $items;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<array{label: string, text: string}>
     */
    private function cashCloseObservations(array $payload): array
    {
        $session = $payload['session'] ?? [];
        $observations = [];

        if (! empty($session['opening_notes'])) {
            $observations[] = ['label' => 'Apertura', 'text' => (string) $session['opening_notes']];
        }

        if (! empty($session['closing_notes'])) {
            $observations[] = ['label' => 'Cierre', 'text' => (string) $session['closing_notes']];
        }

        if ($payload['is_forced_close'] ?? false) {
            $forced = $payload['forced_close'] ?? [];
            if (! empty($forced['forced_close_notes'])) {
                $observations[] = ['label' => 'Admin', 'text' => (string) $forced['forced_close_notes']];
            }
        }

        return $observations;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function buildShiftClose(array $payload, int $paperWidthMm = 80, ?string $printedAt = null): string
    {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $shift = $payload['shift'] ?? [];
        $summary = $payload['summary'] ?? [];
        $managerial = $payload['managerial'] ?? [];
        $lines = [];

        $branchName = (string) ($payload['branch_name'] ?? 'NIGHTPOS');
        $lines[] = $this->center($branchName, $width);
        $lines[] = $this->center('CIERRE DE TURNO', $width);
        $lines[] = $this->center((string) ($shift['name'] ?? 'Turno'), $width);
        $lines[] = str_repeat('=', $width);

        $general = $managerial['general'] ?? [];
        $lines = array_merge($lines, $this->sectionLines('INFORMACION GENERAL', $width, array_filter([
            ['Empresa', (string) ($payload['tenant_name'] ?? '—')],
            ['Sucursal', $branchName],
            ['Turno', (string) ($payload['shift_label'] ?? $shift['name'] ?? '—')],
            ['Administrador', (string) ($payload['closed_by_name'] ?? $shift['closed_by_name'] ?? '—')],
            ['Cajas cerradas', (string) ($general['closed_cash_sessions'] ?? 0)],
            ['Cajeros', $this->truncate(implode(', ', $general['cashiers'] ?? []), $width - 8)],
            ['Fecha', (string) ($shift['business_date'] ?? '—')],
            ($payload['duration_minutes'] ?? null) !== null ? ['Duracion', (string) $payload['duration_minutes'].' min'] : null,
        ])));

        $salesInfo = $managerial['sales'] ?? [];
        $lines = array_merge($lines, $this->sectionLines('RESUMEN GENERAL', $width, [
            ['Venta total', ((string) ($salesInfo['total'] ?? $summary['total_sales'] ?? '0.00')).' BOB'],
            ['Cantidad ventas', (string) ($salesInfo['count'] ?? '0')],
            ['Ticket promedio', ((string) ($salesInfo['average_ticket'] ?? '0.00')).' BOB'],
        ]));

        $paymentStats = $managerial['payment_stats'] ?? [];
        $paymentRows = [];
        foreach (['CASH' => 'Efectivo', 'QR' => 'QR', 'CARD' => 'Tarjeta', 'MIXED' => 'Mixto'] as $key => $label) {
            $row = $paymentStats[$key] ?? ['count' => 0, 'amount' => '0.00', 'percent' => '0.0'];
            $paymentRows[] = [
                $label.' ('.(string) ($row['count'] ?? 0).')',
                ((string) ($row['amount'] ?? '0.00')).' BOB / '.((string) ($row['percent'] ?? '0')).'%',
            ];
        }
        $lines = array_merge($lines, $this->sectionLines('METODOS DE PAGO', $width, $paymentRows));

        $financial = $managerial['financial_result'] ?? [];
        $lines = array_merge($lines, $this->sectionLines('RESULTADO FINANCIERO', $width, [
            ['VENTAS', ((string) ($financial['sales'] ?? '0.00')).' BOB'],
            ['Pagado garzones', ((string) ($financial['paid_waiters'] ?? '0.00')).' BOB'],
            ['Pagado chicas', ((string) ($financial['paid_girls'] ?? '0.00')).' BOB'],
            ['Pagado limpieza', ((string) ($financial['paid_cleaning'] ?? '0.00')).' BOB'],
            ['Egresos caja', ((string) ($financial['cash_expenses'] ?? '0.00')).' BOB'],
            ['TOTAL EGRESOS', ((string) ($financial['total_outflows'] ?? '0.00')).' BOB'],
            ['VENTA NETA', ((string) ($financial['net_sales'] ?? '0.00')).' BOB'],
        ]));

        $settlementsPaid = $managerial['settlements_paid'] ?? [];
        if (($settlementsPaid['grand_total'] ?? '0.00') !== '0.00') {
            $lines = array_merge($lines, $this->sectionLines('LIQUIDACIONES', $width, [
                ['Garzones pagados', (string) ($settlementsPaid['WAITER']['count'] ?? 0).' / '.((string) ($settlementsPaid['WAITER']['total'] ?? '0.00')).' BOB'],
                ['Chicas pagadas', (string) ($settlementsPaid['GIRL']['count'] ?? 0).' / '.((string) ($settlementsPaid['GIRL']['total'] ?? '0.00')).' BOB'],
                ['Limpieza pagada', (string) ($settlementsPaid['CLEANING']['count'] ?? 0).' / '.((string) ($settlementsPaid['CLEANING']['total'] ?? '0.00')).' BOB'],
            ]));
            foreach (['WAITER' => 'GARZONES', 'GIRL' => 'CHICAS', 'CLEANING' => 'LIMPIEZA'] as $key => $title) {
                $people = $settlementsPaid[$key]['people'] ?? [];
                if ($people === []) {
                    continue;
                }
                $peopleRows = [];
                foreach (array_slice($people, 0, 8) as $person) {
                    $peopleRows[] = [
                        $this->truncate((string) ($person['name'] ?? '—'), $width - 12),
                        ((string) ($person['amount'] ?? '0.00')).' BOB',
                    ];
                }
                $lines = array_merge($lines, $this->sectionLines($title, $width, $peopleRows));
            }
        }

        $adjustments = $managerial['settlement_adjustments'] ?? [];
        if (($adjustments['total_discounted'] ?? '0.00') !== '0.00') {
            $lines = array_merge($lines, $this->sectionLines('AJUSTES', $width, [
                ['Multas', ((string) ($adjustments['fines']['amount'] ?? '0.00')).' BOB'],
                ['Limpieza', ((string) ($adjustments['cleaning']['amount'] ?? '0.00')).' BOB'],
                ['Desc. manual', ((string) ($adjustments['manual_discount']['amount'] ?? '0.00')).' BOB'],
                ['Total descontado', ((string) ($adjustments['total_discounted'] ?? '0.00')).' BOB'],
            ]));
        }

        $topProducts = $managerial['top_products'] ?? [];
        if ($topProducts !== []) {
            $productRows = [];
            foreach (array_slice($topProducts, 0, 10) as $index => $product) {
                $productRows[] = [
                    '#'.($index + 1).' '.$this->truncate((string) ($product['product_name'] ?? 'Producto'), $width - 10),
                    ((string) ($product['quantity_sold'] ?? 0)).'u',
                ];
            }
            $lines = array_merge($lines, $this->sectionLines('TOP PRODUCTOS', $width, $productRows));
        }

        $categories = $managerial['categories'] ?? [];
        if ($categories !== []) {
            $categoryRows = [];
            foreach ($categories as $label => $amount) {
                $categoryRows[] = [(string) $label, ((string) $amount).' BOB'];
            }
            $lines = array_merge($lines, $this->sectionLines('CATEGORIAS', $width, $categoryRows));
        }

        $waiters = $managerial['waiters'] ?? [];
        if ($waiters !== []) {
            $waiterRows = [];
            foreach (array_slice($waiters, 0, 5) as $index => $row) {
                $waiterRows[] = [
                    '#'.($index + 1).' '.$this->truncate((string) ($row['name'] ?? '—'), $width - 12),
                    ((string) ($row['sales'] ?? '0.00')).' BOB',
                ];
            }
            $lines = array_merge($lines, $this->sectionLines('GARZONES', $width, $waiterRows));
        }

        $roomServices = $managerial['room_services'] ?? [];
        $shows = $managerial['shows'] ?? [];
        if (($roomServices['count'] ?? 0) > 0 || ($shows['count'] ?? 0) > 0) {
            $lines = array_merge($lines, $this->sectionLines('PIEZAS Y SHOWS', $width, [
                ['Piezas', (string) ($roomServices['count'] ?? 0).' / '.((string) ($roomServices['total'] ?? '0.00')).' BOB'],
                ['Shows', (string) ($shows['count'] ?? 0).' / '.((string) ($shows['total'] ?? '0.00')).' BOB'],
            ]));
        }

        $orders = $managerial['orders'] ?? [];
        if ($orders !== []) {
            $lines = array_merge($lines, $this->sectionLines('COMANDAS', $width, [
                ['Creadas', (string) ($orders['created'] ?? 0)],
                ['Enviadas barra', (string) ($orders['sent_to_bar'] ?? 0)],
                ['Cobradas', (string) ($orders['billed'] ?? 0)],
                ['Canceladas', (string) ($orders['cancelled'] ?? 0)],
                ['Corregidas', (string) ($orders['corrected'] ?? 0)],
                ['Pendientes', (string) ($orders['pending'] ?? 0)],
            ]));
        }

        $incidents = $managerial['incidents'] ?? [];
        if (($incidents['force_close'] ?? 0) > 0
            || ($incidents['corrections'] ?? 0) > 0
            || ($incidents['reprints'] ?? 0) > 0
            || ($incidents['print_errors'] ?? 0) > 0) {
            $lines = array_merge($lines, $this->sectionLines('INCIDENCIAS', $width, [
                ['Force close', (string) ($incidents['force_close'] ?? 0)],
                ['Correcciones', (string) ($incidents['corrections'] ?? 0)],
                ['Reimpresiones', (string) ($incidents['reprints'] ?? 0)],
                ['Errores impresion', (string) ($incidents['print_errors'] ?? 0)],
            ]));
        }

        $kpis = $managerial['kpis'] ?? [];
        if ($kpis !== []) {
            $kpiRows = [];
            if (($kpis['top_waiter'] ?? null) !== null) {
                $kpiRows[] = ['Garzon top', (string) ($kpis['top_waiter']['name'] ?? '—')];
                $kpiRows[] = ['', ((string) ($kpis['top_waiter']['sales'] ?? '0.00')).' BOB'];
            }
            if (($kpis['top_girl'] ?? null) !== null) {
                $kpiRows[] = ['Chica top', (string) ($kpis['top_girl']['name'] ?? '—')];
                $kpiRows[] = ['', ((string) ($kpis['top_girl']['settlement'] ?? '0.00')).' BOB'];
            }
            if (($kpis['top_product'] ?? null) !== null) {
                $kpiRows[] = ['Producto top', $this->truncate((string) ($kpis['top_product']['product_name'] ?? '—'), $width - 12)];
                $kpiRows[] = ['', (string) ($kpis['top_product']['quantity_sold'] ?? 0).' u.'];
            }
            if (($kpis['top_room'] ?? null) !== null) {
                $kpiRows[] = ['Pieza top', (string) ($kpis['top_room']['name'] ?? '—')];
                $kpiRows[] = ['', (string) ($kpis['top_room']['uses'] ?? 0).' usos'];
            }
            if ($kpiRows !== []) {
                $lines = array_merge($lines, $this->sectionLines('KPIs DEL TURNO', $width, $kpiRows));
            }
        }

        if (! empty($shift['closure']['notes'])) {
            $lines = array_merge($lines, $this->sectionLines('OBSERVACIONES', $width, [
                ['Notas', $this->truncate((string) $shift['closure']['notes'], $width - 8)],
            ]));
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->row('Impresa', $this->formatTime((string) ($printedAt ?? now()->toIso8601String())), $width);
        $footer = (string) config('nightpos.printing.ticket_footer', 'Powered by Ribersoft · WhatsApp 67369293');
        foreach ($this->wrap($footer, $width) as $line) {
            $lines[] = $this->center($line, $width);
        }
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function buildCashPersonnelTicket(array $payload, int $paperWidthMm = 80, ?string $printedAt = null): string
    {
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $dashboard = $payload['financial_dashboard'] ?? [];
        $settlementSummary = $dashboard['settlement_summary'] ?? [];
        $cashSummary = $dashboard['cash_summary'] ?? [];
        $page = $payload['personnel_page'] ?? null;

        if (! is_array($page)) {
            $entries = $this->compactPersonnelEntriesFromSummary($settlementSummary);
            $globalTotals = [
                'girls' => (string) ($settlementSummary['totals']['girls_confirmed_total'] ?? '0.00'),
                'waiters' => (string) ($settlementSummary['totals']['waiters_confirmed_total'] ?? '0.00'),
                'cleaning' => (string) ($settlementSummary['totals']['cleaning_confirmed_total'] ?? '0.00'),
                'personnel' => (string) ($cashSummary['cash_expense_settlements'] ?? '0.00'),
            ];

            $page = [
                'page_number' => 1,
                'page_total' => 1,
                'range_start' => count($entries) > 0 ? 1 : 0,
                'range_end' => count($entries),
                'total_records' => count($entries),
                'entries' => $entries,
                'page_subtotal' => $globalTotals['personnel'],
                'global_totals' => $globalTotals,
                'include_global_totals' => true,
            ];
        }

        $lines = [];

        $branchName = (string) ($payload['branch_name'] ?? 'NIGHTPOS');
        $pageNumber = (int) ($page['page_number'] ?? 1);
        $pageTotal = (int) ($page['page_total'] ?? 1);
        $rangeStart = (int) ($page['range_start'] ?? 0);
        $rangeEnd = (int) ($page['range_end'] ?? 0);
        $totalRecords = (int) ($page['total_records'] ?? 0);

        $lines[] = $this->center('NIGHTPOS', $width);
        $lines[] = $this->center("PAGOS PERSONAL {$pageNumber}/{$pageTotal}", $width);
        $lines[] = str_repeat('=', $width);
        $lines[] = $this->cashCloseKeyValueLine('Sucursal', $branchName, $width);
        $lines[] = $this->cashCloseKeyValueLine('Caja', '#'.(string) ($payload['session']['id'] ?? '—'), $width);
        $lines[] = $this->cashCloseKeyValueLine('Cajera', (string) ($payload['cashier_name'] ?? '—'), $width);
        $lines[] = $this->cashCloseKeyValueLine('Impresion', $this->formatDateTimeLong((string) ($printedAt ?? now()->toIso8601String())), $width);
        $lines[] = $this->cashCloseKeyValueLine('Registros', "{$rangeStart}-{$rangeEnd} de {$totalRecords}", $width);

        $entries = is_array($page['entries'] ?? null) ? $page['entries'] : [];
        $activeRole = '';

        foreach ($entries as $entry) {
            $role = (string) ($entry['role'] ?? '');
            if ($role !== $activeRole) {
                $lines[] = str_repeat('-', $width);
                $lines[] = $this->center($this->personnelRoleTitle($role), $width);
                $lines[] = $this->personnelRoleHeader($role);
                $activeRole = $role;
            }

            $lines[] = $this->personnelCompactRow($entry, $role);
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->cashCloseAmountLine('SUBTOTAL PAGINA', (string) ($page['page_subtotal'] ?? '0.00'), $width, false, true);

        if ((bool) ($page['include_global_totals'] ?? false)) {
            $totals = is_array($page['global_totals'] ?? null) ? $page['global_totals'] : [];
            $lines[] = str_repeat('-', $width);
            $lines[] = $this->cashCloseAmountLine('TOTAL CHICAS', (string) ($totals['girls'] ?? '0.00'), $width, false, true);
            $lines[] = $this->cashCloseAmountLine('TOTAL GARZONES', (string) ($totals['waiters'] ?? '0.00'), $width, false, true);
            $lines[] = $this->cashCloseAmountLine('TOTAL LIMPIEZA', (string) ($totals['cleaning'] ?? '0.00'), $width, false, true);
            $lines[] = $this->cashCloseAmountLine('TOTAL PERSONAL', (string) ($totals['personnel'] ?? '0.00'), $width, false, true);
        }

        $lines[] = str_repeat('-', $width);
        $lines[] = $this->center('CONTROL DE PAGO', $width);
        $lines[] = str_repeat('=', $width);
        $lines[] = $this->center('Powered by Ribersoft', $width);
        $lines[] = $this->center('WhatsApp 67369293', $width);
        $lines[] = str_repeat('=', $width);

        return implode("\n", $lines)."\n";
    }

    /**
     * @param array<string, mixed> $settlementSummary
     * @return list<array<string, mixed>>
     */
    private function compactPersonnelEntriesFromSummary(array $settlementSummary): array
    {
        $entries = [];
        $roles = [
            'GIRL' => $settlementSummary['totals']['personnel_rows']['girls'] ?? [],
            'WAITER' => $settlementSummary['totals']['personnel_rows']['waiters'] ?? [],
            'CLEANING' => $settlementSummary['totals']['personnel_rows']['cleaning'] ?? [],
        ];

        foreach ($roles as $role => $rows) {
            usort($rows, static fn (array $a, array $b): int => strcasecmp((string) ($a['staff_name'] ?? ''), (string) ($b['staff_name'] ?? '')));
            $index = 1;
            foreach ($rows as $row) {
                $entries[] = [
                    'role' => $role,
                    'index' => $index,
                    'name' => (string) ($row['staff_name'] ?? '—'),
                    'sale' => (string) ($row['sales_total_amount'] ?? '0.00'),
                    'pay' => (string) ($row['total_amount'] ?? '0.00'),
                ];
                $index++;
            }
        }

        return $entries;
    }

    private function personnelRoleTitle(string $role): string
    {
        return match ($role) {
            'WAITER' => 'GARZONES',
            'CLEANING' => 'LIMPIEZA',
            default => 'CHICAS',
        };
    }

    private function personnelRoleHeader(string $role): string
    {
        return match ($role) {
            'WAITER' => 'NRO  NOMBRE            VENTA      PAGO',
            default => 'NRO  NOMBRE                       PAGO',
        };
    }

    /**
     * @param array<string, mixed> $entry
     */
    private function personnelCompactRow(array $entry, string $role): string
    {
        $index = str_pad((string) ($entry['index'] ?? 0), 2, '0', STR_PAD_LEFT);
        $name = strtoupper($this->truncate((string) ($entry['name'] ?? '—'), $role === 'WAITER' ? 16 : 24));
        $pay = number_format((float) ($entry['pay'] ?? 0), 2, '.', ',');

        if ($role === 'WAITER') {
            $sale = number_format((float) ($entry['sale'] ?? 0), 2, '.', ',');

            return sprintf('%2s   %-16s %8s %9s', $index, $name, $sale, $pay);
        }

        return sprintf('%2s   %-24s %12s', $index, $name, $pay);
    }

    public function buildForType(PrintJobType $type, array $payload, int $paperWidthMm = 80): string
    {
        return match ($type) {
            PrintJobType::OrderCommand => $this->buildOrderCommand(
                $payload['order'] ?? [],
                $payload['waiter_name'] ?? null,
                $payload['service_area_name'] ?? null,
                $paperWidthMm,
                (bool) ($payload['is_reprint'] ?? false),
                isset($payload['correction_number']) ? (int) $payload['correction_number'] : null,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::Precheck => $this->buildPrecheck(
                $payload['order'] ?? [],
                $payload['branch_name'] ?? null,
                $payload['waiter_name'] ?? null,
                $payload['service_area_name'] ?? null,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::SaleReceipt => $this->buildSaleReceipt(
                $payload['sale'] ?? [],
                $payload['order'] ?? null,
                $payload['cashier_name'] ?? null,
                $payload['waiter_name'] ?? null,
                $payload['service_area_name'] ?? null,
                $payload['branch_name'] ?? null,
                $paperWidthMm,
            ),
            PrintJobType::RoomService => $this->buildRoomService(
                $payload['room_service'] ?? [],
                $payload['branch_name'] ?? null,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::ShowTicket => $this->buildShowTicket(
                $payload['show'] ?? [],
                $payload['branch_name'] ?? null,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::CashMovement => $this->buildCashMovement(
                $payload['movement'] ?? [],
                $payload['branch_name'] ?? null,
                $payload['cashier_name'] ?? null,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::CashClose => $this->buildCashCloseSummaryTicket(
                $payload,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::ShiftClose => $this->buildShiftClose(
                $payload,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            PrintJobType::SettlementPayment => $this->buildSettlementPayment(
                $payload,
                $paperWidthMm,
                $payload['printed_at'] ?? null,
            ),
            default => "NightPOS — {$type->value}\n",
        };
    }

    private function paymentModeLabel(string $mode): string
    {
        return match (strtoupper($mode)) {
            'CASH' => 'EFECTIVO',
            'QR' => 'QR',
            'CARD' => 'TARJETA',
            'MIXED' => 'MIXTO',
            default => strtoupper($mode),
        };
    }

    private function resolveLocationLabel(string $tableLabel): string
    {
        $normalized = strtolower(trim($tableLabel));

        if (str_starts_with($normalized, 'pieza') || str_starts_with($normalized, 'habit')) {
            return str_starts_with($normalized, 'habit') ? 'Habitacion' : 'Pieza';
        }

        if (str_starts_with($normalized, 'barra') || str_starts_with($normalized, 'bar ')) {
            return 'Barra';
        }

        if (str_starts_with($normalized, 'vip')) {
            return 'VIP';
        }

        return 'Mesa';
    }

    /**
     * @param  array<string, mixed>  $order
     * @return list<array<string, mixed>>
     */
    private function visibleItems(array $order): array
    {
        return array_values(array_filter(
            $order['items'] ?? [],
            static fn (array $item) => ($item['item_status'] ?? '') !== 'CANCELLED',
        ));
    }

    private function center(string $text, int $width): string
    {
        $len = strlen($text);
        if ($len >= $width) {
            return substr($text, 0, $width);
        }

        $pad = (int) floor(($width - $len) / 2);

        return str_repeat(' ', $pad).$text;
    }

    private function row(string $label, string $value, int $width): string
    {
        $label = $this->truncate($label, 12);
        $space = max(1, $width - strlen($label) - strlen($value));

        return $label.str_repeat(' ', $space).$value;
    }

    private function truncate(string $text, int $max): string
    {
        if (strlen($text) <= $max) {
            return $text;
        }

        return substr($text, 0, max(0, $max - 3)).'...';
    }

    /**
     * @return list<string>
     */
    private function wrap(string $text, int $width): array
    {
        $words = preg_split('/\s+/', trim($text)) ?: [];
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : $current.' '.$word;
            if (strlen($candidate) <= $width) {
                $current = $candidate;
                continue;
            }

            if ($current !== '') {
                $lines[] = $current;
            }
            $current = $word;
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines === [] ? [''] : $lines;
    }

    private function cashCloseKeyValueLine(string $label, string $value, int $width): string
    {
        return $label.': '.$value;
    }

    private function cashCloseAmountLine(string $label, string $value, int $width, bool $compact = false, bool $highlight = false): string
    {
        $formattedValue = is_numeric(str_replace([',', ' '], '', $value))
            ? number_format((float) $value, 2, '.', ',')
            : $value;

        $labelText = $compact ? $label.': ' : $label;
        $targetWidth = $compact ? 24 : 26;
        $dots = max(2, $targetWidth - strlen($labelText) - strlen((string) $formattedValue));
        $line = $labelText.str_repeat('.', $dots).' '.$formattedValue;

        if ($highlight) {
            return $line;
        }

        return $line;
    }

    private function cashCloseCompactLine(string $label, string $value, int $width): string
    {
        return $label.': '.$value;
    }

    private function cashCloseProductHeader(int $width): string
    {
        return 'Cantidad - Producto';
    }

    private function cashCloseProductLine(int $quantity, string $productName, int $width): string
    {
        return str_pad((string) $quantity, 2, ' ', STR_PAD_LEFT).' - '.strtoupper($this->truncate($productName, $width - 5));
    }

    private function formatDateTimeLong(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return (new \DateTimeImmutable($value))->format('d/m/Y H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function formatTime(string $value): string
    {
        try {
            return (new \DateTimeImmutable($value))->format('H:i');
        } catch (\Throwable) {
            return $value;
        }
    }

    private function formatDateTime(string $value): string
    {
        if ($value === '') {
            return '-';
        }

        try {
            return (new \DateTimeImmutable($value))->format('d/m H:i');
        } catch (\Throwable) {
            return $value;
        }
    }
}
