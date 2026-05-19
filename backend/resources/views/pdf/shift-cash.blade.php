<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Cierre de turno #{{ $shiftTurnId }}</title>
    @include('pdf.partials.styles')
    <style>
        .erp-grid { display: table; width: 100%; border-collapse: collapse; margin: 8px 0 14px; font-size: 9pt; }
        .erp-grid th, .erp-grid td { border: 1px solid #ccc; padding: 5px 8px; text-align: left; }
        .erp-grid th { background: #f0f0f0; font-weight: 600; }
        .erp-grid .num { text-align: right; white-space: nowrap; }
        .section-title { font-size: 11pt; margin: 14px 0 6px; border-bottom: 1px solid #999; padding-bottom: 2px; }
        .kpi { font-size: 10pt; margin: 4px 0; }
        .muted { color: #555; font-size: 8pt; }
        .highlight { font-weight: 700; font-size: 10pt; }
    </style>
</head>
<body>
    @php
        $erp = $report['erp_summary'] ?? [];
        $exec = $erp['executive'] ?? [];
        $ct = $report['cash_totals'] ?? [];
        $sh = $report['shift'] ?? [];
        $opening = (int) ($sh['opening_cash'] ?? 0);
    @endphp

    <div class="header-row">
        <h1>Informe de cierre de caja · Turno #{{ $shiftTurnId }}</h1>
        <div class="muted">{{ $appName }} · Documento tipo liquidación ERP</div>
    </div>

    <h2 class="section-title">Indicadores del turno</h2>
    <table class="erp-grid">
        <thead>
            <tr>
                <th>Indicador</th>
                <th class="num">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total vendido</strong> (suma de ítems POS del turno, antes de medios de pago)</td>
                <td class="num highlight">{{ number_format((int) ($exec['total_vendido'] ?? $erp['product_sales_subtotal'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total cobrado</strong> (efectivo + QR + tarjeta registrados en caja)</td>
                <td class="num highlight">{{ number_format((int) ($exec['total_cobrado'] ?? ($erp['payments_collected']['total'] ?? 0)), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total gastos de personal</strong> (comisiones meseros + liquidaciones a chicas)</td>
                <td class="num">{{ number_format((int) ($exec['total_gastos_personal'] ?? (($erp['waiter_commissions_total'] ?? 0) + ($erp['companion_payouts_total'] ?? 0))), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total egresos de caja</strong> (retiros y salidas registradas en el turno)</td>
                <td class="num">{{ number_format((int) ($exec['total_egresos_caja'] ?? ($erp['drawer_all_out'] ?? 0)), 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <p class="kpi muted">Los detalle de cuadre, cobranzas por medio de pago y movimientos siguen abajo.</p>

    @if ($meta)
        <div class="block"><span class="label">Sucursal</span><br>{{ $meta->site_code }} — {{ $meta->site_name }}</div>
        <div class="block"><span class="label">Cajero</span><br>{{ $meta->cashier_name }}</div>
        <div class="block"><span class="label">Período / estado</span><br>{{ $meta->period }} · {{ $meta->status }}</div>
        <div class="block"><span class="label">Apertura</span><br>{{ $meta->opened_at }}</div>
        @if ($meta->closed_at)
            <div class="block"><span class="label">Cierre</span><br>{{ $meta->closed_at }}</div>
        @endif
    @endif

    <h2 class="section-title">Resumen ejecutivo (cuadre)</h2>
    <table class="erp-grid">
        <thead>
            <tr>
                <th>Concepto</th>
                <th class="num">Monto</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Ventas registradas (suma ítems de órdenes del turno)</td>
                <td class="num">{{ number_format((int) ($erp['product_sales_subtotal'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Cobranzas — efectivo (ventas POS)</td>
                <td class="num">{{ number_format((int) ($erp['payments_collected']['cash'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Cobranzas — QR (no físico en caja)</td>
                <td class="num">{{ number_format((int) ($erp['payments_collected']['qr'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Cobranzas — tarjeta (no físico en caja)</td>
                <td class="num">{{ number_format((int) ($erp['payments_collected']['card'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total cobrado (efectivo + QR + tarjeta)</strong></td>
                <td class="num highlight">{{ number_format((int) ($erp['payments_collected']['total'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Comisiones meseros (sobre pagos con comisión)</td>
                <td class="num">{{ number_format((int) ($erp['waiter_commissions_total'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Pagos a chicas (salidas liquidadas — egreso de caja)</td>
                <td class="num">{{ number_format((int) ($erp['companion_payouts_total'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Efectivo inicial en caja</td>
                <td class="num">{{ number_format($opening, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Ingresos manuales a caja</td>
                <td class="num">+ {{ number_format((int) ($erp['drawer_manual_in'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Egresos de caja (retiros + pagos chicas automáticos y manuales)</td>
                <td class="num">− {{ number_format((int) ($erp['drawer_all_out'] ?? 0), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Efectivo esperado en cajón</strong></td>
                <td class="num highlight">{{ number_format((int) ($erp['expected_cash_in_drawer'] ?? ($ct['expected_cash'] ?? 0)), 0, ',', '.') }}</td>
            </tr>
            @if ($meta && $meta->closing_cash !== null)
                <tr>
                    <td>Efectivo contado al cerrar</td>
                    <td class="num">{{ number_format((int) $meta->closing_cash, 0, ',', '.') }}</td>
                </tr>
            @endif
        </tbody>
    </table>
    <p class="kpi muted">
        El QR y la tarjeta entran al negocio pero no al efectivo físico: el arqueo compara solo billetes y monedas
        con el <strong>efectivo esperado</strong> (inicial + ventas caja + ingresos manuales − todo egreso incl. pagos a chicas).
    </p>

    @if (!empty($erp['companion_payouts']))
        <h2 class="section-title">Detalle pagos a chicas (salidas)</h2>
        <table class="erp-grid">
            <thead>
                <tr>
                    <th>Chica</th>
                    <th class="num">Monto</th>
                    <th>Fecha pago</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($erp['companion_payouts'] as $cp)
                    <tr>
                        <td>{{ $cp['stage_name'] ?? '—' }}</td>
                        <td class="num">{{ number_format((int) ($cp['amount'] ?? 0), 0, ',', '.') }}</td>
                        <td>{{ $cp['paid_at'] ?? '—' }}</td>
                        <td>{{ $cp['notes'] ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <h2 class="section-title">Movimientos de caja (detalle)</h2>
    <table class="data">
        <thead><tr><th>Fecha</th><th>Usuario</th><th>Dirección</th><th class="num">Monto</th><th>Notas</th></tr></thead>
        <tbody>
            @foreach ($movements as $m)
                <tr>
                    <td>{{ $m->created_at }}</td>
                    <td>{{ $m->user_name ?? '—' }}</td>
                    <td>{{ $m->direction === 'in' ? 'Ingreso' : 'Egreso' }}</td>
                    <td class="num">{{ number_format((int) $m->amount, 0, ',', '.') }}</td>
                    <td>{{ $m->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
