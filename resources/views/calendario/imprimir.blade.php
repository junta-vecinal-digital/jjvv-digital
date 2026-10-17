<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Horario {{ $periodo === 'mes' ? 'mensual' : 'semanal' }} · JJVV Digital</title>
    <style>
        body { font: 14px Arial, sans-serif; color: #111; margin: 24px auto; max-width: 1000px; padding: 0 16px; }
        h1 { font-size: 24px; margin-bottom: 8px; }
        .acciones { display: flex; flex-wrap: wrap; align-items: center; gap: 16px; margin-bottom: 24px; }
        button { background: #fff; color: #111; border: 1px solid #555; padding: 10px 16px; cursor: pointer; border-radius: 6px; font: inherit; }
        p { line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        caption { text-align: left; margin-bottom: 8px; }
        th, td { border: 1px solid #888; padding: 8px; text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #eee; }
        .horario { white-space: nowrap; }
        .nota { font-size: 12px; }
        thead { display: table-header-group; }
        tr { break-inside: avoid; }
        @page { size: A4 portrait; margin: 15mm; }
        @media print {
            body { font-size: 10pt; margin: 0; padding: 0; max-width: none; }
            .acciones { display: none; }
            h1 { font-size: 18pt; }
            th, td { padding: 6px; }
        }
    </style>
</head>
<body>
    <div class="acciones">
        <button type="button" onclick="window.print()">Imprimir / guardar como PDF</button>
        <a href="{{ route('calendario.index', ['semana' => $desde->toDateString()]) }}">Volver al calendario</a>
    </div>
    <h1>Horario de la sede · {{ $periodo === 'mes' ? 'Mensual' : 'Semanal' }}</h1>
    <p>Del {{ $desde->format('d/m/Y') }} al {{ $hasta->format('d/m/Y') }}<br>
        Emitido el {{ $emitidoEn->format('d/m/Y H:i') }}</p>
    <p class="nota">Las solicitudes pendientes ocupan el horario hasta su confirmación, cancelación o vencimiento. Los horarios fijos programados también ocupan la sede.</p>
    <table>
        <caption>Reservas y horarios fijos del período</caption>
        <thead><tr><th scope="col">Fecha</th><th scope="col">Horario</th><th scope="col">Actividad</th><th scope="col">Estado</th></tr></thead>
        <tbody>
            @foreach ($agenda as $dia)
                @forelse ($dia['filas'] as $fila)
                    <tr>
                        <th scope="row">{{ ucfirst($dia['fecha']->translatedFormat('l d/m/Y')) }}</th>
                        <td class="horario">{{ $fila['inicio'] }} a {{ $fila['fin'] }}</td>
                        <td>{{ $fila['actividad'] }}</td>
                        <td>{{ $fila['estado'] }}</td>
                    </tr>
                @empty
                    <tr><th scope="row">{{ ucfirst($dia['fecha']->translatedFormat('l d/m/Y')) }}</th><td colspan="3">Sin reservas ni horarios fijos programados.</td></tr>
                @endforelse
            @endforeach
        </tbody>
    </table>
</body>
</html>
