<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Traspaso #{{ $transfer->id }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Traspaso de stock</h1>
        <div class="muted">{{ $appName }} · N° interno #{{ $transfer->id }}</div>
    </div>
    <div class="block"><span class="label">Origen</span><br>{{ $transfer->from_site_code }} — {{ $transfer->from_site_name }}</div>
    <div class="block"><span class="label">Destino</span><br>{{ $transfer->to_site_code }} — {{ $transfer->to_site_name }}</div>
    <div class="block"><span class="label">Fecha traspaso</span><br>{{ $transferredAt }}</div>
    <div class="block"><span class="label">Referencia</span><br>{{ $transfer->document_ref ?? '—' }}</div>
    @if ($transfer->created_by_name)
        <div class="block"><span class="label">Registró</span><br>{{ $transfer->created_by_name }}</div>
    @endif
    @if ($transfer->notes)
        <div class="block"><span class="label">Notas</span><br>{{ $transfer->notes }}</div>
    @endif
    <table class="data">
        <thead><tr><th>SKU</th><th>Producto</th><th class="num">Cantidad</th></tr></thead>
        <tbody>
            @foreach ($lines as $ln)
                <tr>
                    <td>{{ $ln->sku }}</td>
                    <td>{{ $ln->product_name }}</td>
                    <td class="num">{{ number_format((int) $ln->quantity, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
