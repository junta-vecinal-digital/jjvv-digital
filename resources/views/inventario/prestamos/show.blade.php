<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Préstamo de {{ $prestamo->item->nombre }}</h1>
            <a href="{{ route('inventario.prestamos.index') }}" class="text-sm underline">Volver a préstamos</a>
        </div>
    </x-slot>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <x-reservas-tabs active="insumos" />
        @if (session('status'))
            <p role="status" class="rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-200">{{ session('status') }}</p>
        @endif
        <x-input-error :messages="$errors->get('devolucion')" />
        <section class="ui-panel p-6">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div><dt class="text-gray-500 dark:text-gray-400">Unidades prestadas</dt><dd>{{ $prestamo->cantidad }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Destinatario</dt><dd>{{ $prestamo->destinatario_nombre }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Responsable</dt><dd>{{ $prestamo->responsable_nombre }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Teléfono</dt><dd>{{ $prestamo->responsable_telefono }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Fecha de solicitud</dt><dd>{{ $prestamo->fecha_solicitud->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Fecha de entrega</dt><dd>{{ $prestamo->fecha_prestamo->format('d/m/Y') }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Estado de salida</dt><dd>{{ \App\Models\Prestamo::CONDICIONES[$prestamo->estado_salida] }}</dd></div>
                <div><dt class="text-gray-500 dark:text-gray-400">Registrado por</dt><dd>{{ $prestamo->registrador?->name ?? 'Cuenta eliminada' }}</dd></div>
            </dl>
        </section>
        @if ($prestamo->fecha_devolucion)
            <section class="ui-panel space-y-3 p-6">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Devolución completa</h2>
                <p>Las {{ $prestamo->cantidad }} unidades se devolvieron el {{ $prestamo->fecha_devolucion->format('d/m/Y') }}. Estado: {{ \App\Models\Prestamo::CONDICIONES[$prestamo->estado_regreso] }}.</p>
                <p>Disponibles nuevamente: {{ $prestamo->cantidad - $prestamo->cantidad_danada }}. Fuera de servicio: {{ $prestamo->cantidad_danada }}.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">Recibió: {{ $prestamo->receptor?->name ?? 'Cuenta eliminada' }}.</p>
            </section>
        @else
            <details class="ui-panel p-6" @if ($errors->any()) open @endif>
                <summary class="cursor-pointer text-lg font-semibold">Registrar devolución completa</summary>
                <p class="my-4 text-sm text-gray-500 dark:text-gray-400">Se devolverán las {{ $prestamo->cantidad }} unidades del préstamo. Indica cuántas vuelven dañadas: quedarán fuera de servicio y no podrán prestarse nuevamente.</p>
                <x-form-wizard :steps="['Devolución']" :fields="[['fecha_devolucion', 'estado_regreso', 'cantidad_danada']]">
                    <form novalidate method="POST" action="{{ route('inventario.prestamos.devolver', $prestamo) }}" class="space-y-4" x-data="{ condicion: @js(old('estado_regreso', 'bueno')) }">
                        @csrf
                        <x-form-step number="0">
                            <div>
                                <x-input-label for="fecha_devolucion" value="Fecha de devolución" />
                                <x-date-input id="fecha_devolucion" name="fecha_devolucion" :value="old('fecha_devolucion', now()->toDateString())" :min="$prestamo->fecha_prestamo->toDateString()" :max="now()->toDateString()" class="mt-1" required />
                                <x-input-error :messages="$errors->get('fecha_devolucion')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="estado_regreso" value="Estado de regreso" />
                                <select id="estado_regreso" name="estado_regreso" x-model="condicion" class="mt-1 block w-full" required>
                                    @foreach (\App\Models\Prestamo::CONDICIONES as $valor => $nombre)<option value="{{ $valor }}">{{ $nombre }}</option>@endforeach
                                </select>
                                <x-input-error :messages="$errors->get('estado_regreso')" class="mt-2" />
                            </div>
                            <div x-cloak x-show="condicion === 'danado'">
                                <x-input-label for="cantidad_danada" value="Unidades dañadas que quedan fuera de servicio" />
                                <x-text-input id="cantidad_danada" name="cantidad_danada" type="number" min="1" :max="$prestamo->cantidad" step="1" :value="old('cantidad_danada', 1)" x-bind:disabled="condicion !== 'danado'" x-bind:required="condicion === 'danado'" class="mt-1 block w-full" />
                                <x-input-error :messages="$errors->get('cantidad_danada')" class="mt-2" />
                            </div>
                            <p>Confirma en el resumen la devolución de todas las unidades. Si alguna falta, registra la devolución cuando se complete la entrega.</p>
                        </x-form-step>
                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </details>
        @endif
    </div>
</x-app-layout>
