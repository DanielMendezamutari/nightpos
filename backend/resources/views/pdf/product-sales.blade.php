<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Productos vendidos</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Productos vendidos</h1>
        <div class="muted">{{ $appName }} @if($site) · {{ $site->code }} — {{ $site->name }} @endif</div>
        @if(!empty($filterLabel))
            <div class="muted block">{{ $filterLabel }}</div>
        @endif
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th class="num">Cantidad</th>
                <th class="num">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->sku }}</td>
                    <td>{{ $r->product_name }}</td>
                    <td class="num">{{ number_format((int) $r->quantity_sold, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $r->total_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
