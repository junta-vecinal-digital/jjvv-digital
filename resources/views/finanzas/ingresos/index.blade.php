<x-app-layout>
    <x-slot name="header"><x-page-heading title="Ingresos" description="Consulta los aportes registrados, sus respaldos y su estado."><a href="{{ route('ingresos.create') }}" class="ui-button ui-button-primary">Registrar ingreso</a></x-page-heading></x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-finanzas-tabs />
        @include('finanzas._avisos')
        <div class="ui-balance"><span>Saldo de caja</span><strong>${{ number_format($saldo, 0, ',', '.') }}</strong></div>
        @include('finanzas._pendientes')
        <h2 class="text-xl font-semibold">Ingresos registrados</h2>
        <section class="ui-panel overflow-x-auto"><table class="ui-table">
            <thead><tr><th>Fecha / concepto</th><th>Monto</th><th>Estado</th><th>Detalle</th></tr></thead>
            @forelse ($ingresos as $movimiento)
                <x-record-row :id="'ingreso_'.$movimiento->id" :columns="4">
                    <x-slot name="summary">
                        <td>{{ $movimiento->fecha_pago->format('d/m/Y') }}<span class="block text-sm font-medium">{{ \App\Models\Ingreso::CONCEPTOS[$movimiento->concepto] ?? $movimiento->concepto }}</span></td>
                        <td class="font-semibold">${{ number_format($movimiento->monto, 0, ',', '.') }}</td>
                        <td><x-status-badge :state="$movimiento->estaAnulado() ? 'cancelada' : ($movimiento->devolucion ? 'devuelto' : 'confirmada')" :label="$movimiento->estaAnulado() ? 'Anulado' : ($movimiento->devolucion ? 'Devuelto' : 'Vigente')" /></td>
                    </x-slot>
                    @include('finanzas._movimiento', ['esEgreso' => false])
                </x-record-row>
            @empty<tbody><tr><td colspan="4">No hay ingresos registrados.</td></tr></tbody>@endforelse
        </table></section>
        {{ $ingresos->links() }}
    </div>
</x-app-layout>
