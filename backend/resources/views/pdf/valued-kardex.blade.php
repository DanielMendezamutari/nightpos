<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kardex valorizado</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Inventario valorizado (resumen)</h1>
        <div class="muted">{{ $appName }} · {{ $site->code }} — {{ $site->name }}</div>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th class="num">Stock</th>
                <th class="num">Costo ref.</th>
                <th class="num">Valorizado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                @if ($r['track_stock'])
                    <tr>
                        <td>{{ $r['sku'] }}</td>
                        <td>{{ $r['name'] }}</td>
                        <td class="num">{{ number_format($r['stock_actual'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($r['ref_unit_cost'], 0, ',', '.') }}</td>
                        <td class="num">{{ number_format($r['stock_valorizado'], 0, ',', '.') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }} · Solo productos con control de stock.</div>
</body>
</html>
