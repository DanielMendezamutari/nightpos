<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Pieza #{{ $service->id }}</title>
    @include('pdf.partials.styles')
</head>
<body>
    <div class="header-row">
        <h1>Servicio por tiempo (pieza)</h1>
        <div class="muted">{{ $appName }} · {{ $site->code }} — {{ $site->name }}</div>
    </div>
    <div class="block"><span class="label">N° servicio</span><br>#{{ $service->id }}</div>
    <div class="block"><span class="label">Estado</span><br>{{ $service->status }}</div>
    <div class="block"><span class="label">Pieza / sala</span><br>{{ $service->room_label ?? '—' }}</div>
    <div class="block"><span class="label">Cliente</span><br>{{ $service->customer_name ?? '—' }}</div>
    <div class="block"><span class="label">Chica</span><br>{{ $service->companion_name ?? '—' }}</div>
    <div class="block"><span class="label">Mozo/a</span><br>{{ $service->waiter_name ?? '—' }}</div>
    <div class="block"><span class="label">Cajero/a (apertura)</span><br>{{ $service->cashier_name ?? '—' }}</div>
    <div class="block"><span class="label">Tarifa / hora</span><br>{{ number_format((int) $service->rate_per_hour, 0, ',', '.') }}</div>
    <div class="block"><span class="label">Planificado / gracia / alerta (min)</span><br>
        {{ $service->planned_minutes !== null ? (int) $service->planned_minutes : '—' }}
        / {{ (int) $service->grace_minutes }}
        / {{ (int) $service->alert_before_minutes }}
    </div>
    <div class="block"><span class="label">Inicio</span><br>{{ $fmtDt($service->started_at) }}</div>
    <div class="block"><span class="label">Cierre</span><br>{{ $fmtDt($service->closed_at) }}</div>
    <div class="block"><span class="label">Min. manual / facturados</span><br>
        {{ $service->manual_minutes !== null ? (int) $service->manual_minutes : '—' }}
        / {{ (int) $service->billed_minutes }}
    </div>
    <div class="block"><span class="label">Subtotal</span><br>{{ number_format((int) $service->subtotal, 0, ',', '.') }}</div>
    <div class="block"><span class="label">Pagado / saldo</span><br>
        {{ number_format($paidTotal, 0, ',', '.') }} / {{ number_format($balanceDue, 0, ',', '.') }}
    </div>
    @if ($service->notes)
        <div class="block"><span class="label">Notas</span><br>{{ $service->notes }}</div>
    @endif

    <h2 style="font-size: 11pt; margin: 12px 0 4px;">Extensiones</h2>
    <table class="data">
        <thead><tr><th>Minutos</th><th>Usuario</th><th>Cuándo</th><th>Nota</th></tr></thead>
        <tbody>
            @forelse ($extensions as $ex)
                <tr>
                    <td class="num">{{ (int) $ex->added_minutes }}</td>
                    <td>{{ $ex->user_name }}</td>
                    <td>{{ $fmtDt($ex->created_at) }}</td>
                    <td>{{ $ex->extension_notes ?? '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="4">Sin extensiones registradas.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2 style="font-size: 11pt; margin: 12px 0 4px;">Pagos</h2>
    <table class="data">
        <thead><tr><th>Método</th><th class="num">Monto</th><th>Fecha</th></tr></thead>
        <tbody>
            @forelse ($payments as $pay)
                <tr>
                    <td>{{ $pay->method }}</td>
                    <td class="num">{{ number_format((int) $pay->amount, 0, ',', '.') }}</td>
                    <td>{{ $fmtDt($pay->paid_at) }}</td>
                </tr>
            @empty
                <tr><td colspan="3">Sin pagos registrados.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="footer">Generado {{ $generatedAt }} · Turno #{{ $service->shift_turn_id }}</div>
</body>
</html>
