<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-semibold">Finanzas · Préstamo de insumos #{{ $solicitud->id }}</h1></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <a class="ui-link" href="{{ route('finanzas.arriendos.index') }}">Volver al registro de garantías</a>
        @if (session('status'))<p class="ui-panel p-4">{{ match (session('status')) { 'garantia-resuelta' => 'Resolución de garantía registrada.', 'garantia-por-devolver' => 'Garantía marcada por devolver.', 'retencion-registrada' => 'Ingreso por retención registrado nuevamente.', default => session('status') } }}</p>@endif
        @if ($errors->any())<div class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <section class="ui-panel p-6 space-y-3">
            <p>Estado: {{ \App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado] }}</p>
            <p>Solicitante: {{ $solicitud->solicitante_nombre }} · {{ $solicitud->grupo_nombre ?? 'Particular' }}</p>
            <p>Dirigente responsable: {{ $solicitud->responsable_nombre }}@if ($solicitud->responsable_cargo) · {{ \App\Models\User::ROLES[$solicitud->responsable_cargo] ?? $solicitud->responsable_cargo }}@endif</p>
            <p>Aporte voluntario por el préstamo completo: <strong>${{ number_format($solicitud->monto, 0, ',', '.') }}</strong></p>
            <p>Garantía separada: <strong>${{ number_format($solicitud->garantia, 0, ',', '.') }}</strong></p>
            <p>Total a recibir: ${{ number_format($solicitud->monto + $solicitud->garantia, 0, ',', '.') }}</p>
            @foreach ($solicitud->lineas as $linea)<p>{{ $linea->nombre_insumo }} · {{ $linea->cantidad }} unidades</p>@endforeach
            @if ($solicitud->fecha_devolucion)<p>Insumos devueltos: {{ $solicitud->fecha_devolucion->format('d/m/Y') }}</p>@endif
            @if ($solicitud->garantiaRecibida)<p>Garantía: {{ \App\Models\Garantia::ESTADOS[$solicitud->garantiaRecibida->estado] }}</p>@endif
            @if (auth()->user()->hasRole('presidente'))<a class="underline" href="{{ route('insumos.show', $solicitud) }}">Ver gestión del préstamo</a>@endif
        </section>
        @if (! $solicitud->pago_registrado_en && ($solicitud->monto > 0 || $solicitud->garantia > 0))<a class="ui-button ui-button-secondary" href="{{ route('finanzas.pendientes.index') }}">Ver ingresos por aprobar</a>@endif
        @if ($solicitud->pago_registrado_en)
            <section class="ui-panel p-6 space-y-3">
                <h2 class="font-semibold">Recepción registrada</h2>
                <p>Fecha: {{ $solicitud->fecha_pago->format('d/m/Y') }} · {{ \App\Models\Ingreso::MEDIOS[$solicitud->medio_pago] }}</p>
                @if ($solicitud->talonario)<p>Talonario: {{ $solicitud->talonario }}</p>@endif
                @if ($solicitud->numero_transaccion)<p>Transacción: {{ $solicitud->numero_transaccion }}</p>@endif
                @if ($solicitud->comprobante_path)<a class="underline" href="{{ route('finanzas.insumos.comprobante', $solicitud) }}" target="_blank" rel="noopener">Ver comprobante del total</a>@endif
                @foreach ($solicitud->ingresos as $ingreso)<p>Ingreso #{{ $ingreso->id }} · ${{ number_format($ingreso->monto, 0, ',', '.') }} · {{ $ingreso->estaAnulado() ? 'Anulado' : 'Vigente' }}</p>@endforeach
                @if ($solicitud->monto > 0 && ! $solicitud->ingresos->contains(fn ($i) => ! $i->estaAnulado()))
                    <p>El ingreso está anulado. Puedes volver a registrarlo con sus datos originales, sin repetir la recepción de garantía.</p><a class="underline" href="{{ route('finanzas.pendientes.index') }}">Revisar reingreso en Ingresos por aprobar</a>
                @endif
            </section>
        @endif
        @if ($solicitud->garantiaRecibida)
            @if ($solicitud->estado !== 'devuelto')<p class="ui-panel p-4">La garantía se resolverá después de registrar la devolución completa de los insumos.</p>@endif
            @include('finanzas._garantia', [
                'garantia' => $solicitud->garantiaRecibida, 'esFinanciero' => $solicitud->estado === 'devuelto',
                'minFecha' => max($solicitud->fecha_pago->toDateString(), $solicitud->fecha_devolucion?->toDateString() ?? $solicitud->fecha_pago->toDateString()),
            ])
        @endif
    </div>
</x-app-layout>
