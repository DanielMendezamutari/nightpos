<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ranking chicas</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Ranking de chicas</h1>
        <div class="muted">{{ $appName }} @if(isset($site) && $site) · {{ $site->code }} — {{ $site->name }} @endif</div>
        @if(!empty($filterLabel))
            <div class="muted block">{{ $filterLabel }}</div>
        @endif
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>Chica</th>
                <th class="num">Bebidas (uds.)</th>
                <th class="num">Total generado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>{{ $r->stage_name }}</td>
                    <td class="num">{{ number_format((float) $r->drinks_count, 0, ',', '.') }}</td>
                    <td class="num">{{ number_format((float) $r->total_generated, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
