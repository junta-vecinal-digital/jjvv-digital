<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Registrar egreso</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="ui-panel p-6 ">
                <x-form-wizard :steps="['Movimiento', 'Comprobante']" :fields="[['concepto','fecha_pago','monto'],['numero_transaccion','pagado_por','comprobante','observaciones']]">
                    <form novalidate method="POST" action="{{ route('egresos.store') }}" enctype="multipart/form-data" class="space-y-4" x-data="{ pagador: @js(old('pagado_por') ? ($pagadores->contains(old('pagado_por')) ? old('pagado_por') : '__otro') : ''), otro: @js(old('pagado_por', '')) }">
                        @csrf

                        <x-form-step number="0" class="ui-form-grid">
                            <div>
                                <x-input-label for="concepto" value="Concepto" />
                                <select id="concepto" name="concepto" required class="mt-1 block w-full rounded-md border-gray-300">
                                    <option value="">Seleccionar</option>
                                    @foreach ($conceptos as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('concepto') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('concepto')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="fecha_pago" value="Fecha de pago" />
                                <x-date-input id="fecha_pago" name="fecha_pago" class="mt-1 block w-full" :value="old('fecha_pago', now()->toDateString())" max="{{ now()->toDateString() }}" required />
                                <x-input-error :messages="$errors->get('fecha_pago')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="monto" value="Monto (CLP)" />
                                <x-text-input id="monto" name="monto" type="number" min="1" max="100000000" step="1" required class="mt-1 block w-full" :value="old('monto')" />
                                <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                            </div>

                        </x-form-step>
                        <x-form-step number="1" class="ui-form-grid">
                            <div>
                                <x-input-label for="numero_transaccion" value="Número de transacción" />
                                <x-text-input id="numero_transaccion" name="numero_transaccion" type="text" required maxlength="50" class="mt-1 block w-full" :value="old('numero_transaccion')" />
                                <x-input-error :messages="$errors->get('numero_transaccion')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="pagador" value="Quién pagó" />
                                <select id="pagador" x-model="pagador" class="mt-1 block w-full" required>
                                    <option value="">Elegir responsable</option>
                                    @foreach ($pagadores as $pagador)
                                        <option value="{{ $pagador }}">{{ $pagador }}</option>
                                    @endforeach
                                    <option value="__otro">Otra persona</option>
                                </select>
                                <input type="hidden" name="pagado_por" :value="pagador === '__otro' ? otro : pagador">
                                <div x-show="pagador === '__otro'" x-cloak class="mt-3">
                                    <x-input-label for="otro_pagador" value="Nombre de quien pagó" />
                                    <x-text-input id="otro_pagador" type="text" x-model="otro" maxlength="100" x-bind:disabled="pagador !== '__otro'" x-bind:required="pagador === '__otro'" class="mt-1 block w-full" />
                                </div>
                                <x-input-error :messages="$errors->get('pagado_por')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="comprobante" value="Copia del comprobante (PDF, JPG o PNG, máx. 5 MB)" />
                                <x-file-input id="comprobante" name="comprobante" required accept=".pdf,.jpg,.jpeg,.png" />
                                <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                            </div>

                            <div>
                                <x-input-label for="observaciones" value="Observaciones (opcional)" />
                                <textarea id="observaciones" name="observaciones" rows="3" maxlength="500" class="mt-1 block w-full rounded-md border-gray-300">{{ old('observaciones') }}</textarea>
                                <x-input-error :messages="$errors->get('observaciones')" class="mt-2" />
                            </div>

                        </x-form-step>

                        <x-wizard-actions :cancel="route('egresos.index')" />
                    </form>
                </x-form-wizard>
            </div>
        </div>
    </div>
</x-app-layout>
