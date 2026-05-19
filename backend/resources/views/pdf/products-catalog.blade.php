<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Catálogo productos</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Catálogo de productos</h1>
        <div class="muted">{{ $appName }} · {{ $site->code }} — {{ $site->name }}</div>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Producto</th>
                <th>Categoría</th>
                <th class="num">Stock</th>
                <th class="num">P. solo</th>
                <th class="num">P. c/chica</th>
                <th class="num">Costo</th>
                <th>Activo</th>
                <th class="num">Vendidas (uds.)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->sku }}</td>
                    <td>{{ $r->name }}</td>
                    <td>{{ $r->category_name ?? '—' }}</td>
                    <td class="num">{{ (bool) $r->track_stock ? number_format((int) $r->stock_actual, 0, ',', '.') : '—' }}</td>
                    <td class="num">{{ number_format((int) $r->price_solo, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $r->price_with_companion, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $r->purchase_price, 0, ',', '.') }}</td>
                    <td>{{ (bool) $r->is_active ? 'Sí' : 'No' }}</td>
                    <td class="num">{{ number_format((int) $r->sold_units, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
