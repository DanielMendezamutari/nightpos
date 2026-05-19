<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Compra #{{ $order->id }}</title>
    <style>
        @page { margin: 24px 32px; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10pt;
            color: #1a1a1a;
            line-height: 1.35;
        }
        h1 {
            font-size: 16pt;
            margin: 0 0 4px 0;
            color: #111;
        }
        .muted { color: #555; font-size: 9pt; }
        .header-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 10px;
        }
        .brand { display: table-cell; vertical-align: top; width: 55%; }
        .meta { display: table-cell; vertical-align: top; text-align: right; font-size: 9pt; }
        .block { margin-bottom: 10px; }
        .label { font-weight: bold; color: #333; }
        table.lines {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
        }
        table.lines th {
            text-align: left;
            border-bottom: 1px solid #333;
            padding: 6px 4px;
            font-size: 8.5pt;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        table.lines td {
            border-bottom: 1px solid #ddd;
            padding: 6px 4px;
            vertical-align: top;
        }
        table.lines td.num { text-align: right; white-space: nowrap; }
        .total-row td {
            border-bottom: none;
            border-top: 1px solid #333;
            font-weight: bold;
            padding-top: 8px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 8px;
            border-top: 1px solid #ddd;
            font-size: 8pt;
            color: #666;
        }
        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9pt;
            font-weight: bold;
        }
        .status-received { background: #e8f4ea; color: #1b5e20; }
        .status-cancelled { background: #fdecea; color: #c62828; }
    </style>
</head>
<body>
    <div class="header-row">
        <div class="brand">
            <h1>Registro de compra</h1>
            <div class="muted">{{ $appName }}</div>
            @if ($siteName || $siteCode)
                <div style="margin-top:6px;">
                    <span class="label">Sucursal:</span>
                    @if ($siteCode){{ $siteCode }} — @endif{{ $siteName ?? '—' }}
                </div>
            @endif
        </div>
        <div class="meta">
            <div><span class="label">N° interno</span> #{{ $order->id }}</div>
            @if ($purchasedAtFormatted)
                <div style="margin-top:4px;"><span class="label">Fecha ingreso</span><br>{{ $purchasedAtFormatted }}</div>
            @endif
            <div style="margin-top:6px;">
                <span class="label">Estado</span><br>
                @if (($order->status ?? 'received') === 'cancelled')
                    <span class="status-badge status-cancelled">Anulada</span>
                @else
                    <span class="status-badge status-received">Recibida</span>
                @endif
            </div>
        </div>
    </div>

    <div class="block">
        <span class="label">Proveedor</span><br>
        {{ $order->supplier_name ?? '—' }}
    </div>
    <div class="block">
        <span class="label">N° documento (referencia)</span><br>
        {{ $order->document_ref ?? '—' }}
    </div>
    @if ($order->created_by_name)
        <div class="block">
            <span class="label">Registró</span><br>
            {{ $order->created_by_name }}
        </div>
    @endif
    @if (($order->status ?? '') === 'cancelled' && $order->cancelled_at)
        <div class="block">
            <span class="label">Anulación</span><br>
            {{ \Illuminate\Support\Carbon::parse($order->cancelled_at)->timezone(config('app.timezone'))->translatedFormat('d/m/Y H:i') }}
            @if ($order->cancelled_by_name)
                — {{ $order->cancelled_by_name }}
            @endif
        </div>
    @endif
    @if ($order->notes)
        <div class="block">
            <span class="label">Notas</span><br>
            {{ $order->notes }}
        </div>
    @endif

    <div class="block" style="margin-top:14px;">
        <span class="label">Detalle de ítems</span>
        <table class="lines">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th>Bulto</th>
                    <th class="num">Cant.</th>
                    <th class="num">Costo u.</th>
                    <th class="num">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($lines as $ln)
                    <tr>
                        <td>{{ $ln['sku'] }}</td>
                        <td>{{ $ln['product_name'] }}</td>
                        <td>{{ $ln['packaging_label'] }}</td>
                        <td class="num">{{ number_format($ln['quantity'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($ln['unit_cost'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($ln['line_total'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" class="num">Total</td>
                    <td class="num">{{ number_format($totalAmount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Documento generado automáticamente con los datos registrados en {{ $appName }}.
        Montos en la moneda configurada del sistema (sin decimales en este resumen).
        <br>Generado: {{ $generatedAtFormatted }}
    </div>
</body>
</html>
