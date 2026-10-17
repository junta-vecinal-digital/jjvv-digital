<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-semibold">Préstamos y garantías · Finanzas</h1></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
        <p>Préstamos aprobados, entregados o devueltos. El cobro se registra como ingreso; la garantía se mantiene separada.</p>
        <section class="ui-panel overflow-x-auto">
            <table class="ui-table"><thead><tr><th>Préstamo</th><th>Solicitante / agrupación</th><th>Cobro</th><th>Garantía</th><th></th></tr></thead><tbody>
                @forelse ($solicitudes as $solicitud)
                    <tr>
                        <td>#{{ $solicitud->id }} · {{ \App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado] }}</td>
                        <td>{{ $solicitud->solicitante_nombre }}@if ($solicitud->grupo_nombre)<br>{{ $solicitud->grupo_nombre }}@endif</td>
                        <td>${{ number_format($solicitud->monto, 0, ',', '.') }}<br>{{ $solicitud->monto == 0 ? 'Gratuito · sin ingreso' : ($solicitud->ingresos->contains(fn ($i) => ! $i->estaAnulado()) ? 'Registrado' : 'Pendiente de registro') }}</td>
                        <td>${{ number_format($solicitud->garantia, 0, ',', '.') }}<br>{{ $solicitud->garantiaRecibida ? \App\Models\Garantia::ESTADOS[$solicitud->garantiaRecibida->estado] : ($solicitud->garantia > 0 ? 'Recepción pendiente de registro' : 'Sin garantía') }}</td>
                        <td><a class="underline" href="{{ route('finanzas.insumos.show', $solicitud) }}">Gestionar</a></td>
                    </tr>
                @empty<tr><td colspan="5">No hay préstamos aprobados para gestionar.</td></tr>@endforelse
            </tbody></table>
        </section>
        {{ $solicitudes->links() }}
    </div>
</x-app-layout>
