<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold">Arriendos y garantías · Finanzas</h2></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
        <p>Reservas confirmadas: los aportes mayores que $0 se registran como ingreso. Las garantías históricas de sede se conservan separadas hasta su devolución o retención.</p>
        <section class="ui-panel overflow-x-auto">
            <table class="ui-table">
                <thead><tr><th>Reserva</th><th>Responsable</th><th>Aporte / pago histórico</th><th>Garantía histórica</th><th></th></tr></thead>
                <tbody>
                    @forelse ($arriendos as $arriendo)
                        <tr>
                            <td>#{{ $arriendo->id }} · {{ $arriendo->inicio->format('d/m/Y H:i') }}<br>{{ \App\Models\Reserva::ESTADOS[$arriendo->estado] }}</td>
                            <td>{{ $arriendo->responsable_nombre ?? $arriendo->socio?->nombre_completo }}@if ($arriendo->grupo)<br>{{ $arriendo->grupo->nombre }}@endif</td>
                            <td>${{ number_format($arriendo->valor_arriendo, 0, ',', '.') }}<br>{{ $arriendo->aporte_variable && $arriendo->valor_arriendo == 0 ? 'Gratuita · sin ingreso' : ($arriendo->ingresos->contains(fn ($ingreso) => ! $ingreso->estaAnulado()) ? 'Registrado' : 'Pendiente de registro') }}</td>
                            <td>@if ($arriendo->garantia)${{ number_format($arriendo->garantia->monto, 0, ',', '.') }}<br>{{ \App\Models\Garantia::ESTADOS[$arriendo->garantia->estado] }}@else No corresponde @endif</td>
                            <td><a class="underline" href="{{ route('arriendos.show', ['reserva' => $arriendo, 'desde' => 'finanzas']) }}">Gestionar</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">No hay arriendos validados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </section>
        {{ $arriendos->links() }}
    </div>
</x-app-layout>
