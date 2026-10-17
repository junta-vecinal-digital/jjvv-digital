<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Emitir certificado en ventanilla</h2>
            <a href="{{ route('admin.certificados.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">← Volver al listado</a>
        </div>
    </x-slot>

    @php
        $cls = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
        $medios = \App\Models\Ingreso::MEDIOS;
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 ui-panel">
                <x-form-wizard :steps="['Persona', 'Destino', 'Verificación']" :fields="[['tipo','socio_id','nombre_solicitante','rut_solicitante','direccion'],['dirigido_a'],['medio_pago','talonario','numero_transaccion','pago_verificado','residencia_6_meses','documentos_verificados']]">
                    <form novalidate method="POST" action="{{ route('admin.certificados.store') }}" class="space-y-6"
                          x-data="{
                              tipo: @js(old('tipo', $socioSel ? 'socio' : 'residencia')),
                              medio: @js(old('medio_pago', 'efectivo')), socioId: @js((string) old('socio_id', $socioSel?->id ?? '')), editarDatos: false,
                              nombre: @js(old('nombre_solicitante', $socioSel?->nombre_completo ?? '')),
                              rut: @js(old('rut_solicitante', $socioSel?->rut ?? '')),
                              direccion: @js(old('direccion', $socioSel?->direccion ?? '')),
                              llenar(e) {
                                  const o = e.target.selectedOptions[0];
                                  this.editarDatos = false; if (!o || !o.value) { this.nombre = ''; this.rut = ''; this.direccion = ''; return; }
                                  this.nombre = o.dataset.nombre; this.rut = o.dataset.rut; this.direccion = o.dataset.direccion;
                              }
                          }">
                        @csrf

                        <x-form-step number="0">
                            <div>
                                <x-input-label for="tipo" value="Tipo de certificado" />
                                <select id="tipo" name="tipo" x-model="tipo" class="{{ $cls }}">
                                    @foreach (\App\Models\Certificado::TIPOS as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                            </div>

                            <div>
                                <x-input-label for="socio_id" value="Socio (obligatorio para certificado de socio; opcional en residencia)" />
                                <select id="socio_id" name="socio_id" x-model="socioId" :required="tipo === 'socio'" x-on:change="llenar($event)" class="{{ $cls }}">
                                    <option value="">— Sin vincular a un socio —</option>
                                    @foreach ($sociosActivos as $s)
                                        <option value="{{ $s->id }}" data-nombre="{{ $s->nombre_completo }}" data-rut="{{ $s->rut }}" data-direccion="{{ $s->direccion }}"
                                                @selected((string) old('socio_id', $socioSel?->id) === (string) $s->id)>
                                            {{ $s->nombre_completo }} — {{ $s->rut }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('socio_id')" />
                            </div>

                            <label x-show="socioId" class="flex items-center gap-2 text-sm">
                                <input type="checkbox" x-model="editarDatos" class="rounded text-brand-500 focus:ring-brand-500">
                                Editar datos solo para este certificado
                            </label>
                            <div>
                                <x-input-label for="nombre_solicitante" value="Nombre completo" />
                                <input id="nombre_solicitante" name="nombre_solicitante" autocomplete="off" type="text" :readonly="!!socioId && !editarDatos" x-model="nombre" class="{{ $cls }}" required>
                                <x-input-error class="mt-2" :messages="$errors->get('nombre_solicitante')" />
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="rut_solicitante" value="RUT" />
                                    <input id="rut_solicitante" name="rut_solicitante" type="text" :readonly="!!socioId && !editarDatos" x-model="rut" placeholder="12345678-5" class="{{ $cls }}" required>
                                    <x-input-error class="mt-2" :messages="$errors->get('rut_solicitante')" />
                                </div>
                                <div>
                                    <x-input-label for="direccion" value="Dirección" />
                                    <input id="direccion" name="direccion" autocomplete="off" type="text" :readonly="!!socioId && !editarDatos" x-model="direccion" class="{{ $cls }}" required>
                                    <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                                </div>
                            </div>

                        </x-form-step>
                        <x-form-step number="1">
                            <div>
                                <x-input-label for="dirigido_a" value="Dirigido a / entidad donde se presentará" />
                                <input id="dirigido_a" name="dirigido_a" list="destinatarios-certificado" autocomplete="off" maxlength="255" type="text" class="{{ $cls }}" required value="{{ old('dirigido_a') }}">
                                <x-input-error class="mt-2" :messages="$errors->get('dirigido_a')" />
                                <x-destinatarios :options="$destinatarios" />
                            </div>

                        </x-form-step>
                        <x-form-step number="2">
                            {{-- Solo residencia: pago y verificaciones --}}
                            <fieldset :disabled="tipo !== 'residencia'" x-show="tipo === 'residencia'" class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    El certificado de residencia cuesta ${{ number_format(\App\Models\Parametro::entero('certificado_residencia_valor'), 0, ',', '.') }}. Registra el pago recibido en este momento.
                                </p>

                                <div>
                                    <x-input-label for="medio_pago" value="Medio de pago" />
                                    <select id="medio_pago" name="medio_pago" x-model="medio" class="{{ $cls }}">
                                        @foreach ($medios as $valor => $etiqueta)
                                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('medio_pago')" />
                                </div>

                                <div x-show="medio === 'efectivo'">
                                    <x-input-label for="talonario" value="Número de talonario" />
                                    <input id="talonario" name="talonario" type="text" :disabled="medio !== 'efectivo'" :required="tipo === 'residencia' && medio === 'efectivo'" class="{{ $cls }}" value="{{ old('talonario') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('talonario')" />
                                </div>

                                <div x-show="medio === 'transferencia'">
                                    <x-input-label for="numero_transaccion" value="Número de transacción" />
                                    <input id="numero_transaccion" name="numero_transaccion" type="text" :disabled="medio !== 'transferencia'" :required="tipo === 'residencia' && medio === 'transferencia'" maxlength="50" class="{{ $cls }}" value="{{ old('numero_transaccion') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('numero_transaccion')" />
                                </div>

                                <div>
                                    <label class="inline-flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="pago_verificado" value="1" :required="tipo === 'residencia'" @checked(old('pago_verificado'))
                                               class="mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-brand-600 shadow-sm focus:ring-brand-500">
                                        <span>Confirmo que recibí el pago de ${{ number_format(\App\Models\Parametro::entero('certificado_residencia_valor'), 0, ',', '.') }}.</span>
                                    </label>
                                    <x-input-error class="mt-2" :messages="$errors->get('pago_verificado')" />
                                </div>

                                <div>
                                    <label class="inline-flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="residencia_6_meses" value="1" :required="tipo === 'residencia'" @checked(old('residencia_6_meses'))
                                               class="mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-brand-600 shadow-sm focus:ring-brand-500">
                                        <span>Confirmo que la persona acredita al menos 6 meses de residencia.</span>
                                    </label>
                                    <x-input-error class="mt-2" :messages="$errors->get('residencia_6_meses')" />
                                </div>

                                <div>
                                    <label class="inline-flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="documentos_verificados" value="1" :required="tipo === 'residencia'" @checked(old('documentos_verificados'))
                                               class="mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-brand-600 shadow-sm focus:ring-brand-500">
                                        <span>Verifiqué en persona el carnet de identidad y la boleta de luz o agua de la persona.</span>
                                    </label>
                                    <x-input-error class="mt-2" :messages="$errors->get('documentos_verificados')" />
                                </div>
                            </fieldset>
                            <p x-show="tipo !== 'residencia'" class="text-sm text-gray-500 dark:text-gray-400">Este tipo de certificado no requiere pago. Revisa los datos y pulsa Aceptar para emitirlo.</p>

                        </x-form-step>

                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </div>
        </div>
    </div>
</x-app-layout>
