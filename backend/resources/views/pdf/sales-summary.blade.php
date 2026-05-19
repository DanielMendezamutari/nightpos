<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de ventas</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Reporte de ventas</h1>
        <div class="muted">{{ $appName }} @if($site) · {{ $site->code }} — {{ $site->name }} @endif</div>
        @if(!empty($filterLabel))
            <div class="muted block">{{ $filterLabel }}</div>
        @endif
    </div>
    <div class="block">
        <span class="label">Total cobrado:</span>
        {{ number_format($grandTotal, 0, ',', '.') }}
        &nbsp;—&nbsp;
        <span class="label">Pagos:</span> {{ number_format($paymentsCount, 0, ',', '.') }}
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Día</th>
                <th class="num">Pagos</th>
                <th class="num">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byDay as $r)
                <tr>
                    <td>{{ $r->day }}</td>
                    <td class="num">{{ number_format((int) $r->payments_count, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $r->total_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
