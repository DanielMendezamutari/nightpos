<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden #{{ $order->id }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Comanda / orden POS</h1>
        <div class="muted">{{ $appName }} · {{ $site->code }} — {{ $site->name }}</div>
    </div>
    <div class="block"><span class="label">N° orden</span><br>#{{ $order->id }}</div>
    <div class="block"><span class="label">Mesa / zona</span><br>{{ $order->table_code ?? '—' }} @if($order->zone_code) ({{ $order->zone_code }}) @endif</div>
    <div class="block"><span class="label">Mozo/a</span><br>{{ $order->waiter_name }}</div>
    <div class="block"><span class="label">Estado</span><br>{{ $order->status }}</div>
    <div class="block"><span class="label">Pedido</span><br>{{ $order->ordered_at }}</div>
    <table class="data">
        <thead><tr><th>SKU</th><th>Producto</th><th>Modo</th><th class="num">Cant.</th><th class="num">P. unit.</th><th class="num">Subtotal</th></tr></thead>
        <tbody>
            @foreach ($items as $it)
                <tr>
                    <td>{{ $it->sku }}</td>
                    <td>{{ $it->product_name }}</td>
                    <td>{{ $it->consumption_type }}</td>
                    <td class="num">{{ (int) $it->quantity }}</td>
                    <td class="num">{{ number_format((int) $it->unit_price, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $it->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="block"><span class="label">Total ítems</span><br>{{ number_format($subtotal, 0, ',', '.') }}</div>
    <div class="footer">Generado {{ $generatedAt }} · Turno #{{ $order->shift_turn_id }}</div>
</body>
</html>
