<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Kardex {{ $product->sku }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Kardex de movimientos</h1>
        <div class="muted">{{ $appName }}</div>
    </div>
    <div class="block"><span class="label">Sucursal</span><br>{{ $site->code ?? '' }} — {{ $site->name ?? '' }}</div>
    <div class="block"><span class="label">Producto</span><br>{{ $product->sku }} — {{ $product->name }}</div>
    <div class="block"><span class="label">Stock actual en sucursal</span><br>{{ number_format($stockActual, 0, ',', '.') }}</div>
    <table class="data">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th class="num">Δ</th>
                <th class="num">Saldo</th>
                <th>Ref.</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ \Illuminate\Support\Carbon::parse($r['moved_at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') }}</td>
                    <td>{{ $r['movement_type'] }}</td>
                    <td class="num">{{ $r['delta'] }}</td>
                    <td class="num">{{ $r['running_stock'] }}</td>
                    <td>{{ $r['reference_type'] ?? '—' }} {{ $r['reference_id'] ?? '' }}</td>
                    <td>{{ $r['notes'] ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
