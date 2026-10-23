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
                <x-form-wizard :steps="['Persona', 'Destino', 'Verificación']" :fields="[['tipo','socio_id','nombre_solicitante','rut_solicitante','direccion'],['dirigido_a'],['aporte','medio_pago','talonario','numero_transaccion','pago_verificado','residencia_6_meses','documentos_verificados']]">
                    <form novalidate method="POST" action="{{ route('admin.certificados.store') }}" class="space-y-6"
                          x-data="{
                              tipo: @js(old('tipo', $socioSel ? 'socio' : 'residencia')),
                              aporte: @js(old('aporte', \App\Models\Parametro::entero('certificado_residencia_valor'))), medio: @js(old('medio_pago', 'efectivo')), socioId: @js((string) old('socio_id', $socioSel?->id ?? '')), editarDatos: false,
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

                        <x-form-step number="0" class="ui-form-grid">
                            <div>
                                <x-input-label for="tipo" value="Tipo de certificado" />
                                <select id="tipo" name="tipo" x-model="tipo" class="{{ $cls }}">
                                    @foreach (\App\Models\Certificado::TIPOS as $valor => $etiqueta)
                                        <option value="{{ $valor }}" @selected(old('tipo') === $valor)>{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                                <x-input-error class="mt-2" :messages="$errors->get('tipo')" />
                                <p x-show="tipo === 'socio'" class="mt-2 text-sm">Este certificado de socio será solo para imprimir en ventanilla. No aparecerá en las descargas de su titular.</p>
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

                            <div class="grid gap-6 sm:col-span-2 sm:grid-cols-2">
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
                        <x-form-step number="1" class="ui-form-grid">
                            <div>
                                <x-input-label for="dirigido_a" value="Dirigido a / entidad donde se presentará" />
                                <input id="dirigido_a" name="dirigido_a" list="destinatarios-certificado" autocomplete="off" maxlength="255" type="text" class="{{ $cls }}" required value="{{ old('dirigido_a') }}">
                                <x-input-error class="mt-2" :messages="$errors->get('dirigido_a')" />
                                <x-destinatarios :options="$destinatarios" />
                            </div>

                        </x-form-step>
                        <x-form-step number="2" class="ui-form-grid">
                            {{-- Solo residencia: pago y verificaciones --}}
                            <fieldset :disabled="tipo !== 'residencia'" x-show="tipo === 'residencia'" class="space-y-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    Con $0 no se requiere número de transacción ni verificación del aporte.
                                </p>

                                <div>
                                    <x-input-label for="aporte" value="Aporte recibido (CLP)" />
                                    <x-text-input id="aporte" name="aporte" type="number" x-model="aporte" min="0" max="100000000" step="1" x-bind:required="tipo === 'residencia'" />
                                    <x-input-error :messages="$errors->get('aporte')" />
                                </div>
                                <div x-show="Number(aporte) > 0">
                                    <x-input-label for="medio_pago" value="Medio del aporte" />
                                    <select id="medio_pago" name="medio_pago" x-model="medio" :disabled="Number(aporte) <= 0" class="{{ $cls }}">
                                        @foreach ($medios as $valor => $etiqueta)
                                            <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error class="mt-2" :messages="$errors->get('medio_pago')" />
                                </div>

                                <div x-show="Number(aporte) > 0 && medio === 'efectivo'">
                                    <x-input-label for="talonario" value="Número de talonario" />
                                    <input id="talonario" name="talonario" type="text" :disabled="Number(aporte) <= 0 || medio !== 'efectivo'" :required="tipo === 'residencia' && Number(aporte) > 0 && medio === 'efectivo'" class="{{ $cls }}" value="{{ old('talonario') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('talonario')" />
                                </div>

                                <div x-show="Number(aporte) > 0 && medio === 'transferencia'">
                                    <x-input-label for="numero_transaccion" value="Número de transacción" />
                                    <input id="numero_transaccion" name="numero_transaccion" type="text" x-bind:required="Number(aporte) > 0" :disabled="Number(aporte) <= 0 || medio !== 'transferencia'"  maxlength="50" class="{{ $cls }}" value="{{ old('numero_transaccion') }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('numero_transaccion')" />
                                </div>
                                <div x-show="Number(aporte) > 0">
                                    <label class="inline-flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                                        <input type="checkbox" name="pago_verificado" value="1" :disabled="Number(aporte) <= 0" :required="tipo === 'residencia' && Number(aporte) > 0" @checked(old('pago_verificado'))
                                               class="mt-0.5 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-brand-600 shadow-sm focus:ring-brand-500">
                                        <span>Confirmo que recibí el aporte indicado.</span>
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
                            <p x-show="tipo !== 'residencia'" class="text-sm text-gray-500 dark:text-gray-400">Este tipo de certificado no requiere aporte. Revisa los datos y pulsa Aceptar para emitirlo.</p>

                        </x-form-step>

                        <x-wizard-actions :cancel="route('admin.certificados.index')" />
                    </form>
                </x-form-wizard>
            </div>
        </div>
    </div>
</x-app-layout>
