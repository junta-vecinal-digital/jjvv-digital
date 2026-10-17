<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Mis certificados</h2>
    </x-slot>

    @php
        $cls = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'certificado-solicitado')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Tu solicitud fue enviada. Cuando la directiva la apruebe podrás descargar el certificado desde esta página.
                </div>
            @endif

            <div class="p-4 sm:p-8 ui-panel">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Solicitar un certificado</h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Para el certificado de residencia debes presentar en la sede tu carnet de identidad y una boleta de luz o agua.
                    El aporte voluntario es de ${{ number_format(\App\Models\Parametro::entero('certificado_residencia_valor'), 0, ',', '.') }}.
                </p>

                <x-form-wizard :steps="['Datos', 'Destino', 'Comprobante']" :fields="[['tipo','nombre_solicitante','rut_solicitante','direccion'],['dirigido_a'],['numero_transaccion','comprobante']]">
                    <form novalidate method="POST" action="{{ route('certificados.store') }}" enctype="multipart/form-data" class="mt-6 space-y-6">
                        @csrf
                        <input type="hidden" name="tipo" value="residencia">

                        <x-form-step number="0">
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="nombre_solicitante" value="Nombre completo" />
                                    <input id="nombre_solicitante" name="nombre_solicitante" autocomplete="name" type="text" required class="{{ $cls }}"
                                           value="{{ old('nombre_solicitante', $socio?->nombre_completo ?? auth()->user()->name) }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('nombre_solicitante')" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="rut_solicitante" value="RUT" />
                                        <input id="rut_solicitante" name="rut_solicitante" type="text" required class="{{ $cls }}" placeholder="12345678-5"
                                               value="{{ old('rut_solicitante', $socio?->rut) }}">
                                        <x-input-error class="mt-2" :messages="$errors->get('rut_solicitante')" />
                                    </div>
                                    <div>
                                        <x-input-label for="direccion" value="Dirección" />
                                        <input id="direccion" name="direccion" autocomplete="street-address" type="text" required class="{{ $cls }}"
                                               value="{{ old('direccion', $socio?->direccion) }}">
                                        <x-input-error class="mt-2" :messages="$errors->get('direccion')" />
                                    </div>
                                </div>
                            </div>

                        </x-form-step>
                        <x-form-step number="1">
                            <div>
                                <x-input-label for="dirigido_a" value="¿Dónde o ante quién lo presentarás?" />
                                <input id="dirigido_a" name="dirigido_a" list="destinatarios-certificado" autocomplete="off" maxlength="255" type="text" class="{{ $cls }}" required
                                       placeholder="Ej.: Registro Social de Hogares, Municipalidad" value="{{ old('dirigido_a') }}">
                                <x-input-error class="mt-2" :messages="$errors->get('dirigido_a')" />
                                <x-destinatarios :options="$destinatarios" />
                            </div>

                        </x-form-step>
                        <x-form-step number="2">
                            <div>
                                <x-input-label for="numero_transaccion" :value="'Número de transacción (transferencia de $'.number_format(\App\Models\Parametro::entero('certificado_residencia_valor'), 0, ',', '.').')'" />
                                <x-text-input id="numero_transaccion" name="numero_transaccion" type="text" required maxlength="50" class="mt-1 block w-full" :value="old('numero_transaccion')" />
                                <x-input-error :messages="$errors->get('numero_transaccion')" class="mt-2" />
                            </div>

                            <div class="mt-4">
                                <x-input-label for="comprobante" value="Comprobante de transferencia (PDF, JPG o PNG, máx. 5 MB)" />
                                <input id="comprobante" name="comprobante" type="file" required accept=".pdf,.jpg,.jpeg,.png" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" />
                                <x-input-error :messages="$errors->get('comprobante')" class="mt-2" />
                            </div>

                        </x-form-step>

                        <x-wizard-actions />
                    </form>
                </x-form-wizard>
            </div>

            <div class="ui-panel overflow-x-auto">
                <table class="ui-table divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-left text-gray-600 dark:text-gray-300">
                        <tr>
                            <th class="px-4 py-3 font-medium">Certificado</th>
                            <th class="px-4 py-3 font-medium">Presentado ante</th>
                            <th class="px-4 py-3 font-medium">Folio</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 text-gray-800 dark:text-gray-200">
                        @forelse ($certificados as $c)
                            <tr>
                                <td class="px-4 py-3">{{ $c->etiquetaTipo() }}<div class="text-xs text-gray-500">{{ $c->created_at->format('d-m-Y') }}</div></td>
                                <td class="px-4 py-3">{{ $c->dirigido_a }}</td>
                                <td class="px-4 py-3">{{ $c->folio ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <x-estado-certificado :estado="$c->estado" />
                                    @if ($c->estado === 'emitido')
                                        <div class="text-xs text-gray-500 mt-1">Vence {{ $c->fecha_vencimiento->format('d-m-Y') }}</div>
                                    @elseif ($c->observaciones && in_array($c->estado, ['rechazado', 'anulado']))
                                        <div class="text-xs text-gray-500 mt-1">{{ $c->observaciones }}</div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @if ($c->puedeDescargarse())
                                        <a href="{{ route('certificados.descargar', $c) }}" target="_blank" class="underline text-brand-600 dark:text-brand-400">
                                            Descargar PDF
                                        </a>
                                        @if ($c->tipo === 'residencia')
                                            <span class="block text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                Disponible hasta {{ $c->descarga_disponible_hasta->format('d-m-Y H:i') }}
                                            </span>
                                        @endif
                                    @elseif ($c->tipo === 'residencia' && $c->estado === 'emitido')
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            Plazo de descarga vencido. Puedes retirarlo en la sede.
                                        </span>
                                    @else
                                        <span class="text-xs text-gray-500 dark:text-gray-400">—</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">Aún no has solicitado certificados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
