<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Recetas de relleno</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Recetas de relleno</h1>
        <div class="muted">{{ $appName }} · {{ $site->code }} — {{ $site->name }}</div>
    </div>
    <table class="data">
        <thead>
            <tr>
                <th>ID</th>
                <th>Origen (stock)</th>
                <th class="num">Uds. origen</th>
                <th>Destino (venta)</th>
                <th class="num">Uds. destino</th>
                <th>Activa</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $r)
                <tr>
                    <td>#{{ $r->id }}</td>
                    <td>{{ $r->source_name }}</td>
                    <td class="num">{{ (int) $r->source_units }}</td>
                    <td>{{ $r->target_name }}</td>
                    <td class="num">{{ (int) $r->target_units }}</td>
                    <td>{{ $r->is_active ? 'Sí' : 'No' }}</td>
                    <td>{{ $r->notes ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
