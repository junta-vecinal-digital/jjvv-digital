<x-app-layout>
    <x-slot name="header"><h1 class="text-2xl font-semibold">Finanzas · Préstamo de insumos #{{ $solicitud->id }}</h1></x-slot>
    <div class="max-w-4xl mx-auto px-4 py-6 space-y-6">
        <a class="underline" href="{{ route('finanzas.insumos.index') }}">Volver a préstamo de insumos</a>
        @if (session('status'))<p class="ui-panel p-4">{{ match (session('status')) { 'garantia-resuelta' => 'Resolución de garantía registrada.', 'garantia-por-devolver' => 'Garantía marcada por devolver.', 'retencion-registrada' => 'Ingreso por retención registrado nuevamente.', default => session('status') } }}</p>@endif
        @if ($errors->any())<div class="ui-panel p-4 text-red-600">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <section class="ui-panel p-6 space-y-3">
            <p>Estado: {{ \App\Models\SolicitudInsumo::ESTADOS[$solicitud->estado] }}</p>
            <p>Solicitante: {{ $solicitud->solicitante_nombre }} · {{ $solicitud->grupo_nombre ?? 'Particular' }}</p>
            <p>Dirigente responsable: {{ $solicitud->responsable_nombre }}</p>
            <p>Aporte voluntario por el préstamo completo: <strong>${{ number_format($solicitud->monto, 0, ',', '.') }}</strong></p>
            <p>Garantía separada: <strong>${{ number_format($solicitud->garantia, 0, ',', '.') }}</strong></p>
            <p>Total a recibir: ${{ number_format($solicitud->monto + $solicitud->garantia, 0, ',', '.') }}</p>
            @foreach ($solicitud->lineas as $linea)<p>{{ $linea->nombre_insumo }} · {{ $linea->cantidad }} unidades</p>@endforeach
            @if ($solicitud->fecha_devolucion)<p>Insumos devueltos: {{ $solicitud->fecha_devolucion->format('d/m/Y') }}</p>@endif
            @if ($solicitud->garantiaRecibida)<p>Garantía: {{ \App\Models\Garantia::ESTADOS[$solicitud->garantiaRecibida->estado] }}</p>@endif
            @if (auth()->user()->hasRole('presidente'))<a class="underline" href="{{ route('insumos.show', $solicitud) }}">Ver gestión del préstamo</a>@endif
        </section>
        @if ($solicitud->monto == 0 && $solicitud->garantia == 0)
            <p class="ui-panel p-4">Préstamo gratuito sin garantía: no genera movimientos financieros.</p>
        @elseif (! $solicitud->pago_registrado_en)
            <section class="ui-panel p-6 space-y-4">
                <h2 class="font-semibold">Registrar aporte y recepción de garantía</h2>
                <p>Confirma cuando hayas recibido el total indicado. Si el aporte voluntario es $0, solo se registrará la garantía, sin crear ingresos.</p>
                <x-form-wizard :steps="['Recepción del aporte']" :fields="[['fecha_pago', 'medio_pago', 'talonario', 'numero_transaccion', 'comprobante']]">
                    <form method="POST" enctype="multipart/form-data" action="{{ route('finanzas.insumos.registrar', $solicitud) }}" class="space-y-4" novalidate x-data="{ medio: @js(old('medio_pago', 'transferencia')) }">
                        @csrf
                        <x-form-step number="0">
                            <div><x-input-label for="fecha_pago" value="Fecha del aporte recibido" /><x-date-input id="fecha_pago" name="fecha_pago" :value="old('fecha_pago', now()->toDateString())" :min="$solicitud->created_at->toDateString()" :max="now()->toDateString()" required /></div>
                            <div class="mt-4"><x-input-label for="medio_pago" value="Medio del aporte" /><select id="medio_pago" name="medio_pago" x-model="medio" class="ui-input w-full" required>@foreach (\App\Models\Ingreso::MEDIOS as $clave => $etiqueta)<option value="{{ $clave }}">{{ $etiqueta }}</option>@endforeach</select></div>
                            <div x-show="medio === 'efectivo'" class="mt-4"><x-input-label for="talonario" value="Número de talonario" /><x-text-input id="talonario" name="talonario" :value="old('talonario')" maxlength="30" x-bind:required="medio === 'efectivo'" x-bind:disabled="medio !== 'efectivo'" /></div>
                            <div x-show="medio === 'transferencia'" class="mt-4"><x-input-label for="numero_transaccion" value="Número de transacción (si no adjuntas comprobante)" /><x-text-input id="numero_transaccion" name="numero_transaccion" :value="old('numero_transaccion')" maxlength="50" x-bind:disabled="medio !== 'transferencia'" /></div>
                            <div class="mt-4"><x-input-label for="comprobante" value="Comprobante del total recibido" /><x-file-input id="comprobante" name="comprobante" accept="application/pdf,image/jpeg,image/png" /><p class="text-sm mt-2">PDF, JPG o PNG, máximo 5 MB. Para transferencias se requiere este archivo o el número de transacción; para efectivo, el talonario.</p></div>
                        </x-form-step>
                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </section>
        @endif
        @if ($solicitud->pago_registrado_en)
            <section class="ui-panel p-6 space-y-3">
                <h2 class="font-semibold">Recepción registrada</h2>
                <p>Fecha: {{ $solicitud->fecha_pago->format('d/m/Y') }} · {{ \App\Models\Ingreso::MEDIOS[$solicitud->medio_pago] }}</p>
                @if ($solicitud->talonario)<p>Talonario: {{ $solicitud->talonario }}</p>@endif
                @if ($solicitud->numero_transaccion)<p>Transacción: {{ $solicitud->numero_transaccion }}</p>@endif
                @if ($solicitud->comprobante_path)<a class="underline" href="{{ route('finanzas.insumos.comprobante', $solicitud) }}" target="_blank" rel="noopener">Ver comprobante del total</a>@endif
                @foreach ($solicitud->ingresos as $ingreso)<p>Ingreso #{{ $ingreso->id }} · ${{ number_format($ingreso->monto, 0, ',', '.') }} · {{ $ingreso->estaAnulado() ? 'Anulado' : 'Vigente' }}</p>@endforeach
                @if ($solicitud->monto > 0 && ! $solicitud->ingresos->contains(fn ($i) => ! $i->estaAnulado()))
                    <p>El ingreso está anulado. Puedes volver a registrarlo con sus datos originales, sin repetir la recepción de garantía.</p>
                    <form method="POST" action="{{ route('finanzas.insumos.reingresar', $solicitud) }}">@csrf<x-primary-button>Volver a registrar aporte</x-primary-button></form>
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
