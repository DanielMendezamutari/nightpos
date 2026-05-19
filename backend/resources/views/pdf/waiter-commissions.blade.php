<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comisiones mozos</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Comisiones de mozos</h1>
        <div class="muted">{{ $appName }} @if($site) · {{ $site->code }} — {{ $site->name }} @endif</div>
        @if(!empty($filterLabel))
            <div class="muted block">{{ $filterLabel }}</div>
        @endif
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Mozo/a</th>
                <th class="num">Base facturada</th>
                <th class="num">Comisión total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->waiter_name }}</td>
                    <td class="num">{{ number_format((int) $r->billed_base, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $r->commission_total, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
