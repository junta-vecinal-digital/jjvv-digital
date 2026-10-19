<x-app-layout>
    <x-slot name="header"><x-page-heading title="Ingresos por aprobar" description="Revisa las operaciones pendientes y registra su recepción. La garantía se conserva separada del ingreso." /></x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status'))<p role="status" class="ui-panel p-4">Registro financiero actualizado.</p>@endif
        @if ($errors->any())<div role="alert" class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <section class="ui-panel overflow-x-auto">
            <h2 class="p-5 text-lg font-semibold">Reservas de sede</h2>
            <table class="ui-table"><thead><tr><th>Reserva / solicitante</th><th>Aporte</th><th>Garantía</th><th>Respaldo</th><th>Acción</th></tr></thead><tbody>
                @forelse ($reservas as $reserva)
                    <tr>
                        <td>#{{ $reserva->id }} · {{ $reserva->responsable_nombre ?? $reserva->socio?->nombre_completo }}<br>{{ $reserva->inicio->format('d/m/Y H:i') }}<br>{{ \App\Models\Reserva::ESTADOS[$reserva->estado] }}</td>
                        <td>
                            ${{ number_format($reserva->valor_arriendo, 0, ',', '.') }}
                            @if ($reserva->ingresos->contains(fn ($i) => ! $i->estaAnulado()))
                                <br>Ya registrado
                            @endif
                        </td>
                        <td>${{ number_format($reserva->garantia?->monto ?? 0, 0, ',', '.') }}</td>
                        <td>
                            @if ($reserva->comprobante_arriendo_path)
                                <a href="{{ route('arriendos.comprobante', $reserva) }}" target="_blank" rel="noopener">Ver comprobante</a>
                            @else
                                Sin archivo
                            @endif
                        </td>
                        <td>@include('finanzas._aprobar-pendiente', ['tipo' => 'sede', 'registro' => $reserva])</td>
                    </tr>
                @empty<tr><td colspan="5">No hay reservas pendientes de registro financiero.</td></tr>@endforelse
            </tbody></table>
        </section>
        {{ $reservas->links() }}
        <section class="ui-panel overflow-x-auto">
            <h2 class="p-5 text-lg font-semibold">Préstamos de insumos</h2>
            <table class="ui-table"><thead><tr><th>Préstamo / solicitante</th><th>Aporte</th><th>Garantía</th><th>Estado</th><th>Acción</th></tr></thead><tbody>
                @forelse ($prestamos as $solicitud)
                    <tr>
                        <td>
                            #{{ $solicitud->id }} · {{ $solicitud->solicitante_nombre }}
                            @if ($solicitud->grupo_nombre)<br>{{ $solicitud->grupo_nombre }}@endif
                            <br>
                            @foreach ($solicitud->lineas as $linea)
                                {{ $linea->nombre_insumo }} × {{ $linea->cantidad }}@if (! $loop->last), @endif
                            @endforeach
                        </td>
                        <td>${{ number_format($solicitud->monto, 0, ',', '.') }}</td>
                        <td>${{ number_format($solicitud->garantia, 0, ',', '.') }}</td>
                        <td>{{ \App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado] }}</td>
                        <td>@include('finanzas._aprobar-pendiente', ['tipo' => 'insumos', 'registro' => $solicitud])</td>
                    </tr>
                @empty<tr><td colspan="5">No hay préstamos pendientes de registro financiero.</td></tr>@endforelse
            </tbody></table>
        </section>
        {{ $prestamos->links() }}
    </div>
</x-app-layout>
