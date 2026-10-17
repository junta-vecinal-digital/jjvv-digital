<x-app-layout>
    <x-slot name="header"><h2 class="text-2xl font-semibold">Arriendos y garantías · Finanzas</h2></x-slot>
    <div class="max-w-7xl mx-auto px-4 py-6 space-y-4">
        <p>Arriendos con pago validado. El pago del arriendo se registra como ingreso; la garantía se conserva separada hasta su devolución o retención.</p>
        <section class="ui-panel overflow-x-auto">
            <table class="ui-table">
                <thead><tr><th>Arriendo</th><th>Responsable</th><th>Pago del arriendo</th><th>Garantía</th><th></th></tr></thead>
                <tbody>
                    @forelse ($arriendos as $arriendo)
                        <tr>
                            <td>#{{ $arriendo->id }} · {{ $arriendo->inicio->format('d-m-Y H:i') }}<br>{{ \App\Models\Reserva::ESTADOS[$arriendo->estado] }}</td>
                            <td>{{ $arriendo->socio?->nombre_completo }}@if ($arriendo->grupo)<br>{{ $arriendo->grupo->nombre }}@endif</td>
                            <td>${{ number_format($arriendo->valor_arriendo, 0, ',', '.') }}<br>{{ $arriendo->ingresos->contains(fn ($ingreso) => ! $ingreso->estaAnulado()) ? 'Registrado' : 'Pendiente de registro' }}</td>
                            <td>${{ number_format($arriendo->valor_garantia, 0, ',', '.') }}<br>{{ \App\Models\Garantia::ESTADOS[$arriendo->garantia->estado] }}</td>
                            <td><a class="underline" href="{{ route('arriendos.show', $arriendo) }}">Gestionar</a></td>
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
