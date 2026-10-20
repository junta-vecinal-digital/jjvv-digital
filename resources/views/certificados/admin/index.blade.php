<x-app-layout>
    <x-slot name="header">
        <x-page-heading title="Gestión de certificados" description="Revisa solicitudes o emite un certificado para imprimir en ventanilla.">
            <a href="{{ route('admin.certificados.create') }}" class="ui-button ui-button-primary">Emitir en ventanilla</a>
        </x-page-heading>
    </x-slot>

    @php $cls = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm'; @endphp

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

            <form method="GET" class="flex flex-wrap gap-2 px-4 sm:px-0">
                <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Nombre, RUT o folio" class="{{ $cls }}">
                <select name="estado" class="{{ $cls }}">
                    <option value="">Todos los estados</option>
                    @foreach (\App\Models\Certificado::ESTADOS as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('estado') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <select name="tipo" class="{{ $cls }}">
                    <option value="">Todos los tipos</option>
                    @foreach (\App\Models\Certificado::TIPOS as $valor => $etiqueta)
                        <option value="{{ $valor }}" @selected(request('tipo') === $valor)>{{ $etiqueta }}</option>
                    @endforeach
                </select>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <x-operation-notice />
            <section class="ui-panel overflow-x-auto"><table class="ui-table"><thead><tr><th>Solicitante / tipo</th><th>Folio / fecha</th><th>Estado</th><th>Acción</th></tr></thead>
                @forelse($certificados as $certificado)
                    <x-record-row :id="'certificado_'.$certificado->id" :columns="4" :label="$certificado->estado === 'solicitado' ? 'Revisar solicitud' : 'Ver certificado'">
                        <x-slot name="summary"><td><strong>{{ $certificado->nombre_solicitante }}</strong><span class="ui-sub">{{ $certificado->etiquetaTipo() }} · Presentado ante: {{ $certificado->dirigido_a }}</span></td><td>{{ $certificado->folio ?? 'Sin folio' }}<span class="ui-sub">{{ ($certificado->fecha_emision ?? $certificado->created_at)->format('d/m/Y') }}</span></td><td><x-estado-certificado :estado="$certificado->estado" /></td></x-slot>
                        @include('certificados.admin._detalle')
                    </x-record-row>
                @empty<tbody><tr><td colspan="4">No hay certificados con esos filtros.</td></tr></tbody>@endforelse
            </table></section>

            <div class="px-4 sm:px-0">{{ $certificados->links() }}</div>
        </div>
    </div>
</x-app-layout>
