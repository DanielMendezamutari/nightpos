<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Sucursal {{ $site->code }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Datos legales y de sucursal</h1>
        <div class="muted">{{ $appName }} · {{ $site->code }} — {{ $site->name }}</div>
    </div>
    <div class="block"><span class="label">Activa</span><br>{{ $site->is_active ? 'Sí' : 'No' }}</div>
    <div class="block"><span class="label">Documento</span><br>{{ $site->legal_document_type ?? '—' }} {{ $site->legal_document_number ?? '' }}</div>
    <div class="block"><span class="label">Razón social</span><br>{{ $site->legal_name ?? '—' }}</div>
    <div class="block"><span class="label">Dirección</span><br>{{ $site->branch_address ?? '—' }}</div>
    <div class="block"><span class="label">Teléfono / email</span><br>{{ $site->branch_phone ?? '—' }} · {{ $site->branch_email ?? '—' }}</div>
    <div class="block"><span class="label">Actividad económica</span><br>{{ $site->economic_activity ?? '—' }}</div>
    <div class="block"><span class="label">Autorización</span><br>
        {{ $site->authorization_date?->format('d/m/Y') ?? '—' }}
        — {{ $site->authorization_resolution ?? '—' }}
    </div>
    <div class="block"><span class="label">Encargado/a</span><br>
        {{ $site->manager_document_type ?? '' }} {{ $site->manager_document_number ?? '' }}
        @if($site->manager_full_name)<br>{{ $site->manager_full_name }} @endif
    </div>
    <div class="block"><span class="label">Moneda</span><br>{{ $site->currency_code ?? '—' }}</div>
    <div class="block"><span class="label">Series (inicio)</span><br>
        Ticket {{ $site->ticket_series_start ?? '—' }} ·
        Boleta {{ $site->boleta_series_start ?? '—' }} ·
        Factura {{ $site->factura_series_start ?? '—' }}
    </div>
    <div class="footer">Generado {{ $generatedAt }}</div>
</body>
</html>
