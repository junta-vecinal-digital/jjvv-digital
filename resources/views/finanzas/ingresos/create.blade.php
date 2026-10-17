<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Registrar ingreso</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="ui-panel p-6 ">
                <x-form-wizard :steps="['Movimiento', 'Pago']" :fields="[['concepto','tipo_cuota','fecha_pago','monto'],['medio_pago','talonario','numero_transaccion','observaciones']]">
                    <form novalidate method="POST" action="{{ route('ingresos.store') }}" class="space-y-4"
                          x-data="{ concepto: @js(old('concepto', '')), medio: @js(old('medio_pago', 'efectivo')) }">
                        @csrf

                        <x-form-step number="0">
                            <div>
                                <x-input-label for="concepto" value="Concepto" />
                                <select id="concepto" name="concepto" required x-model="concepto" class="mt-1 block w-full rounded-md border-gray-300">
                                    <option value="">Seleccionar</option>
                                    @foreach ($conceptos as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('concepto') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('concepto')" class="mt-2" />
                            </div>

                            <div x-show="['cuota_socios', 'aporte_grupos'].includes(concepto)" x-cloak>
                                <x-input-label for="tipo_cuota" value="Tipo de cuota o aporte" />
                                <select id="tipo_cuota" name="tipo_cuota" :disabled="!['cuota_socios', 'aporte_grupos'].includes(concepto)" :required="['cuota_socios', 'aporte_grupos'].includes(concepto)" class="mt-1 block w-full rounded-md border-gray-300">
                                    <option value="">Seleccionar</option>
                                    @foreach ($tiposCuota as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('tipo_cuota') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('tipo_cuota')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fecha_pago" value="Fecha de pago" />
                                <x-text-input id="fecha_pago" name="fecha_pago" type="date" class="mt-1 block w-full" :value="old('fecha_pago', now()->toDateString())" max="{{ now()->toDateString() }}" required />
                                <x-input-error :messages="$errors->get('fecha_pago')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="monto" value="Monto (CLP)" />
                                <x-text-input id="monto" name="monto" type="number" min="1" max="100000000" step="1" required class="mt-1 block w-full" :value="old('monto')" />
                                <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                            </div>

                        </x-form-step>
                        <x-form-step number="1">
                            <div>
                                <x-input-label for="medio_pago" value="Medio de pago" />
                                <select id="medio_pago" name="medio_pago" x-model="medio" required class="mt-1 block w-full rounded-md border-gray-300">
                                    @foreach ($medios as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('medio_pago') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('medio_pago')" class="mt-2" />
                            </div>

                            <div x-show="medio === 'efectivo'" x-cloak>
                                <x-input-label for="talonario" value="Número de talonario (si es efectivo)" />
                                <x-text-input id="talonario" name="talonario" type="text" x-bind:disabled="medio !== 'efectivo'" x-bind:required="medio === 'efectivo'" class="mt-1 block w-full" :value="old('talonario')" />
                                <x-input-error :messages="$errors->get('talonario')" class="mt-2" />
                            </div>

                            <div x-show="medio === 'transferencia'" x-cloak>
                                <x-input-label for="numero_transaccion" value="Número de transacción (si es transferencia)" />
                                <x-text-input id="numero_transaccion" name="numero_transaccion" type="text" x-bind:disabled="medio !== 'transferencia'" x-bind:required="medio === 'transferencia'" class="mt-1 block w-full" :value="old('numero_transaccion')" />
                                <x-input-error :messages="$errors->get('numero_transaccion')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="observaciones" value="Observaciones (obligatorias si el concepto es Otros)" />
                                <textarea id="observaciones" name="observaciones" x-bind:required="concepto === 'otros'" rows="3" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300">{{ old('observaciones') }}</textarea>
                                <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                            </div>

                        </x-form-step>

                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </div>
        </div>
    </div>
</x-app-layout>
