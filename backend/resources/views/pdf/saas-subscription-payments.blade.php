<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pagos SaaS</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Pagos de suscripción SaaS</h1>
        <div class="muted">{{ $appName }} @if($site) · {{ $site->code }} — {{ $site->name }} (ID {{ $site->id }}) @else · Sucursal #{{ $siteId }} @endif</div>
    </div>
    @if (!$site)
        <p class="muted">Sucursal no encontrada.</p>
    @endif
    <table class="data">
        <thead>
            <tr>
                <th>Fecha pago</th>
                <th class="num">Monto</th>
                <th class="num">Meses</th>
                <th class="num">Base</th>
                <th class="num">Dto. %</th>
                <th class="num">Final</th>
                <th>Nota</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $r)
                <tr>
                    <td>{{ $r->paid_at }}</td>
                    <td class="num">{{ number_format((int) $r->amount, 0, ',', '.') }}</td>
                    <td class="num">{{ (int) $r->months_covered }}</td>
                    <td class="num">{{ isset($r->base_amount) ? number_format((int) $r->base_amount, 0, ',', '.') : '—' }}</td>
                    <td class="num">{{ isset($r->discount_percent) ? (int) $r->discount_percent : '—' }}</td>
                    <td class="num">{{ isset($r->final_amount) ? number_format((int) $r->final_amount, 0, ',', '.') : '—' }}</td>
                    <td>{{ $r->note ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7">Sin pagos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
