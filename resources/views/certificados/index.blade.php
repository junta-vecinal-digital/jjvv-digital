<x-app-layout>
    <x-slot name="header"><x-page-heading title="Mis certificados" description="Consulta tus certificados y realiza trámites con los datos de tu ficha activa.">@unless(request('crear'))<a class="ui-button ui-button-primary" href="{{ route('certificados.index',['crear'=>1]) }}">Nuevo certificado</a>@endunless</x-page-heading></x-slot>

    @php
        if (old('tipo') === 'socio' && $errors->getBag('certificadoSocio')->any()) $errors->put('default', $errors->getBag('certificadoSocio'));
        $cls = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-brand-500 dark:focus:border-brand-400 focus:ring-brand-500 dark:focus:ring-brand-400 rounded-md shadow-sm';
    @endphp

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            @if (session('status') === 'certificado-solicitado')
                <div class="p-4 bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-200 rounded-lg text-sm">
                    Tu solicitud fue enviada. Cuando la directiva la apruebe podrás descargar el certificado desde esta página.
                </div>
            @endif

            @if (session('status') === 'certificado-socio-emitido')
                <p class="ui-panel p-4 text-green-700 dark:text-green-400">Tu certificado de socio ya fue emitido. Puedes descargarlo desde el listado.</p>
            @endif
            <x-operation-notice />
            @if(request('crear'))
            <div class="p-4 sm:p-8 ui-panel">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Nuevo certificado</h3>

                <x-form-wizard :steps="['Datos', 'Destino', 'Aporte']" :fields="[['tipo','nombre_solicitante','rut_solicitante','direccion'],['dirigido_a'],['numero_transaccion']]">
                    <form novalidate method="POST" action="{{ route('certificados.store') }}" class="mt-6 space-y-6" x-data="{ tipo: @js(old('tipo','socio')), nombre: @js(old('nombre_solicitante',$socio->nombre_completo)), rut: @js(old('rut_solicitante',$socio->rut)), direccion: @js(old('direccion',$socio->direccion)), original: @js(['nombre'=>$socio->nombre_completo,'rut'=>$socio->rut,'direccion'=>$socio->direccion]), aporte: @js(\App\Models\Parametro::entero('certificado_residencia_valor')) }">
                        @csrf


                        <x-form-step number="0"><div><x-input-label for="tipo" value="Tipo de certificado" /><select id="tipo" name="tipo" x-model="tipo" @change="if (tipo === 'socio') { nombre = original.nombre; rut = original.rut; direccion = original.direccion; }" class="ui-input w-full" required><option value="socio">Socio</option><option value="residencia">Residencia</option></select></div>
                            <div class="space-y-6">
                                <div>
                                    <x-input-label for="nombre_solicitante" value="Nombre completo" />
                                    <input id="nombre_solicitante" name="nombre_solicitante" x-model="nombre" autocomplete="name" type="text" required :readonly="tipo === 'socio'" class="{{ $cls }}"
                                           value="{{ old('nombre_solicitante', $socio?->nombre_completo ?? auth()->user()->name) }}">
                                    <x-input-error class="mt-2" :messages="$errors->get('nombre_solicitante')" />
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                    <div>
                                        <x-input-label for="rut_solicitante" value="RUT" />
                                        <input id="rut_solicitante" name="rut_solicitante" x-model="rut" type="text" required :readonly="tipo === 'socio'" class="{{ $cls }}" placeholder="12345678-5"
                                               value="{{ old('rut_solicitante', $socio?->rut) }}">
                                        <x-input-error class="mt-2" :messages="$errors->get('rut_solicitante')" />
                                    </div>
                                    <div>
                                        <x-input-label for="direccion" value="Dirección" />
                                        <input id="direccion" name="direccion" x-model="direccion" autocomplete="street-address" type="text" required :readonly="tipo === 'socio'" class="{{ $cls }}"
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
                        <x-form-step number="2"><p x-show="tipo === 'socio'" class="ui-notice">El certificado de socio no requiere aporte. Puedes continuar.</p><fieldset x-show="tipo === 'residencia'" :disabled="tipo !== 'residencia'" class="space-y-5">
                            <x-datos-transferencia :monto="\App\Models\Parametro::entero('certificado_residencia_valor')" />
                            <div x-show="Number(aporte) > 0">
                                <x-input-label for="numero_transaccion" value="Número de transacción del aporte" />
                                <x-text-input id="numero_transaccion" name="numero_transaccion" type="text" x-bind:required="Number(aporte) > 0" x-bind:disabled="Number(aporte) <= 0" maxlength="50" class="mt-1 block w-full" :value="old('numero_transaccion')" />
                                <x-input-error :messages="$errors->get('numero_transaccion')" class="mt-2" />
                            </div>

                        </fieldset></x-form-step>

                        <x-wizard-actions :cancel="route('certificados.index')" submit-label="Confirmar trámite" />
                    </form>
                </x-form-wizard>
            </div>

            @else
            <section class="ui-panel overflow-x-auto"><table class="ui-table"><thead><tr><th>Certificado / fecha</th><th>Presentado ante</th><th>Estado</th><th>Detalle</th></tr></thead>
            @forelse($certificados as $c)<x-record-row :id="'personal_'.$c->id" :columns="4"><x-slot name="summary"><td><strong>{{ $c->etiquetaTipo() }}</strong><span class="ui-sub">{{ $c->created_at->format('d/m/Y') }}</span></td><td>{{ $c->dirigido_a }}</td><td><x-estado-certificado :estado="$c->estado" /></td></x-slot><h2 class="text-xl font-semibold">{{ $c->etiquetaTipo() }}</h2><dl class="ui-meta"><div><dt>Titular</dt><dd>{{ $c->nombre_solicitante }}</dd></div><div><dt>RUT</dt><dd>{{ $c->rut_solicitante }}</dd></div><div><dt>Dirección</dt><dd>{{ $c->direccion }}</dd></div><div><dt>Folio</dt><dd>{{ $c->folio ?? 'Sin folio' }}</dd></div><div><dt>Presentado ante</dt><dd>{{ $c->dirigido_a }}</dd></div><div><dt>Vigencia</dt><dd>{{ $c->fecha_vencimiento?->format('d/m/Y') ?? 'Sin emitir' }}</dd></div></dl>
            @if($c->puedeDescargarse())<a href="{{ route('certificados.descargar',$c) }}" class="ui-button ui-button-primary" style="color: white" target="_blank" rel="noopener">Descargar PDF</a>@if($c->tipo === 'residencia')<p class="text-sm text-gray-500">Disponible hasta {{ $c->descarga_disponible_hasta->format('d/m/Y H:i') }}.</p>@endif
            @elseif($c->tipo === 'residencia' && $c->estado === 'emitido')<p class="ui-notice">{{ $c->descargas->isEmpty() ? 'El plazo de descarga venció. Puedes retirarlo en la sede.' : 'El plazo de descarga venció y existe una descarga registrada. Corresponde solicitar un nuevo certificado.' }}</p>
            @elseif($c->observaciones)<p class="ui-notice">{{ $c->observaciones }}</p>@else<p>Solicitud pendiente de revisión de antecedentes y del aporte, si corresponde.</p>@endif
            </x-record-row>@empty<tbody><tr><td colspan="4">Aún no tienes certificados.</td></tr></tbody>@endforelse</table></section>
            @endif
        </div>
    </div>
</x-app-layout>
