<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ventas por personal</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Ventas por personal (mozos)</h1>
        <div class="muted">{{ $appName }} @if($site) · {{ $site->code }} — {{ $site->name }} @endif</div>
        @if(!empty($filterLabel))
            <div class="muted block">{{ $filterLabel }}</div>
        @endif
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Personal</th>
                <th class="num">Unidades</th>
                <th class="num">Total líneas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->staff_name }}</td>
                    <td class="num">{{ number_format((int) $r->quantity_sold, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((int) $r->total_amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
