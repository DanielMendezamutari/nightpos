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
        $width = $paperWidthMm <= 58 ? 32 : 48;
        $session = $payload['session'] ?? [];
        $summary = $payload['summary'] ?? [];
        $lines = [];

        $branchName = (string) ($payload['branch_name'] ?? 'NIGHTPOS');
        $lines[] = $this->center($branchName, $width);
        $lines[] = $this->center('CIERRE DE CAJA', $width);
        $lines[] = $this->center('Caja #'.(string) ($session['id'] ?? '—'), $width);
        $lines[] = str_repeat('=', $width);

        foreach ($this->cashCloseBannerLines($payload, $width) as $bannerLine) {
            $lines[] = $bannerLine;
        }

        $lines = array_merge($lines, $this->sectionLines('INFORMACION GENERAL', $width, array_filter([
            ['Empresa', (string) ($payload['tenant_name'] ?? '—')],
            ['Sucursal', $branchName],
            ['Caja', '#'.(string) ($session['id'] ?? '—')],
            ['Turno', (string) ($payload['shift_label'] ?? '—')],
            ['Cajera', (string) ($payload['cashier_name'] ?? '—')],
            ($payload['admin_name'] ?? '') !== '' ? ['Administrador', (string) $payload['admin_name']] : null,
            ['Apertura', $this->formatDateTime((string) ($session['opened_at'] ?? ''))],
            ['Cierre', $this->formatDateTime((string) ($session['closed_at'] ?? ''))],
            ($payload['duration_minutes'] ?? null) !== null ? ['Duracion', (string) $payload['duration_minutes'].' min'] : null,
        ])));

        $operational = $payload['operational'] ?? [];
        $salesInfo = $operational['sales'] ?? [];
        $totalSales = (string) ($summary['total_sales'] ?? '0.00');
        $lines = array_merge($lines, $this->sectionLines('RESUMEN DE VENTAS', $width, [
            ['Total vendido', ((string) ($salesInfo['total'] ?? $totalSales)).' BOB'],
            ['Cantidad ventas', (string) ($salesInfo['count'] ?? '0')],
            ['Ticket promedio', ((string) ($salesInfo['average_ticket'] ?? '0.00')).' BOB'],
        ]));

        $difference = (string) ($summary['cash_difference'] ?? $session['difference_amount'] ?? '0.00');

        $paymentStats = $operational['payment_stats'] ?? [];
        $paymentRows = [];
        foreach (['CASH' => 'Efectivo', 'QR' => 'QR', 'CARD' => 'Tarjeta', 'MIXED' => 'Mixto'] as $key => $label) {
            $row = $paymentStats[$key] ?? ['count' => 0, 'amount' => '0.00'];
            $paymentRows[] = [$label.' ('.(string) ($row['count'] ?? 0).')', ((string) ($row['amount'] ?? '0.00')).' BOB'];
        }
        $lines = array_merge($lines, $this->sectionLines('METODOS DE PAGO', $width, $paymentRows));

        $cashRows = [
            ['Monto inicial', ((string) ($summary['opening_cash'] ?? $session['opening_amount'] ?? '0.00')).' BOB'],
            ['Efectivo esperado', ((string) ($summary['expected_cash'] ?? $session['expected_amount'] ?? '0.00')).' BOB'],
        ];

        if (! ($payload['is_forced_close'] ?? false)) {
            $cashRows[] = ['Efectivo declarado', ((string) ($summary['counted_cash'] ?? $session['declared_closing_amount'] ?? '0.00')).' BOB'];
        } else {
            $cashRows[] = ['Efectivo declarado', 'Sin arqueo'];
        }

        $cashRows[] = ['Diferencia', ($payload['is_forced_close'] ?? false) && ($summary['counted_cash'] ?? null) === null
            ? 'Sin arqueo'
            : $difference.' BOB'];
        $cashRows[] = ['QR esperado', ((string) ($summary['expected_qr'] ?? '0.00')).' BOB'];
        $cashRows[] = ['Tarjeta esperada', ((string) ($summary['expected_card'] ?? '0.00')).' BOB'];
        $lines = array_merge($lines, $this->sectionLines('ARQUEO', $width, $cashRows));

        $movementsSummary = $operational['movements_summary'] ?? [];
        $movementDetailRows = [
            ['Ingresos', ((string) ($movementsSummary['income_total'] ?? '0.00')).' BOB'],
            ['Egresos', ((string) ($movementsSummary['expense_total'] ?? '0.00')).' BOB'],
        ];
        foreach ($operational['movements'] ?? [] as $movement) {
            $label = ((string) ($movement['movement_type'] ?? '')) === 'INCOME' ? 'Ing.' : 'Egr.';
            $reason = (string) ($movement['reason'] ?? '');
            $movementDetailRows[] = [
                $label.' '.$this->truncate($reason !== '' ? $reason : 'Movimiento', $width - 12),
                ((string) ($movement['amount'] ?? '0.00')).' BOB',
            ];
        }
        if (($operational['movements'] ?? []) !== []) {
            $lines = array_merge($lines, $this->sectionLines('MOVIMIENTOS DE CAJA', $width, $movementDetailRows));
        }

        $settlementsPaid = $operational['settlements_paid'] ?? [];
        if (($settlementsPaid['grand_total'] ?? '0.00') !== '0.00') {
            $settlementRows = [
                ['Garzones', ((string) ($settlementsPaid['WAITER']['total'] ?? '0.00')).' BOB ('.(string) ($settlementsPaid['WAITER']['count'] ?? 0).')'],
                ['Chicas', ((string) ($settlementsPaid['GIRL']['total'] ?? '0.00')).' BOB ('.(string) ($settlementsPaid['GIRL']['count'] ?? 0).')'],
                ['Limpieza', ((string) ($settlementsPaid['CLEANING']['total'] ?? '0.00')).' BOB ('.(string) ($settlementsPaid['CLEANING']['count'] ?? 0).')'],
                ['TOTAL PAGADO', ((string) ($settlementsPaid['grand_total'] ?? '0.00')).' BOB'],
            ];
            $lines = array_merge($lines, $this->sectionLines('LIQUIDACIONES PAGADAS', $width, $settlementRows));
            foreach (['WAITER' => 'GARZONES', 'GIRL' => 'CHICAS', 'CLEANING' => 'LIMPIEZA'] as $key => $title) {
                $people = $settlementsPaid[$key]['people'] ?? [];
                if ($people === []) {
                    continue;
                }
                $peopleRows = [];
                foreach ($people as $person) {
                    $peopleRows[] = [
                        $this->truncate((string) ($person['name'] ?? '—'), $width - 12),
                        ((string) ($person['amount'] ?? '0.00')).' BOB',
                    ];
                }
                $lines = array_merge($lines, $this->sectionLines($title, $width, $peopleRows));
            }
        }

        $adjustments = $operational['settlement_adjustments'] ?? [];
        if (($adjustments['fines']['count'] ?? 0) > 0
            || ($adjustments['cleaning']['count'] ?? 0) > 0
            || ($adjustments['manual_discount']['count'] ?? 0) > 0) {
            $lines = array_merge($lines, $this->sectionLines('AJUSTES LIQUIDACIONES', $width, [
                ['Multas', ((string) ($adjustments['fines']['amount'] ?? '0.00')).' BOB ('.(string) ($adjustments['fines']['count'] ?? 0).')'],
                ['Limpieza desc.', ((string) ($adjustments['cleaning']['amount'] ?? '0.00')).' BOB ('.(string) ($adjustments['cleaning']['count'] ?? 0).')'],
                ['Desc. manual', ((string) ($adjustments['manual_discount']['amount'] ?? '0.00')).' BOB ('.(string) ($adjustments['manual_discount']['count'] ?? 0).')'],
            ]));
        }

        $pending = $operational['pending'] ?? [];
        if (($pending['settlements'] ?? 0) > 0
            || ($pending['orders'] ?? 0) > 0
            || ($pending['room_services'] ?? 0) > 0
            || ($pending['shows'] ?? 0) > 0) {
            $lines = array_merge($lines, $this->sectionLines('PENDIENTES', $width, [
                ['Liquidaciones', (string) ($pending['settlements'] ?? 0)],
                ['Comandas', (string) ($pending['orders'] ?? 0)],
                ['Piezas', (string) ($pending['room_services'] ?? 0)],
                ['Shows', (string) ($pending['shows'] ?? 0)],
            ]));
        }

        $incidents = $this->cashCloseIncidents($payload, $difference);
        if ($incidents !== []) {
            $lines = array_merge($lines, $this->sectionLines('INCIDENCIAS', $width, array_map(
                static fn (string $text) => ['', $text],
                $incidents,
            )));
        }

        $observations = $this->cashCloseObservations($payload);
        if ($observations !== []) {
            $observationRows = [];
            foreach ($observations as $row) {
                $observationRows[] = [$row['label'], $this->truncate($row['text'], $width - 8)];
            }
            $lines = array_merge($lines, $this->sectionLines('OBSERVACIONES', $width, $observationRows));
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
            PrintJobType::CashClose => $this->buildCashClose(
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

        return substr($text, 0, max(0, $max - 1)).'…';
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
            return '—';
        }

        try {
            return (new \DateTimeImmutable($value))->format('d/m H:i');
        } catch (\Throwable) {
            return $value;
        }
    }
}
