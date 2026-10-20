<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Registrar acta</h1>
            <a href="{{ route('actas.index') }}" class="text-sm underline">Volver a actas</a>
        </div>
    </x-slot>

    <div class="mx-auto max-w-3xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="ui-panel p-6">
            <x-form-wizard :steps="['Reunión', 'Acuerdos y escaneo']" :fields="[['fecha', 'tipo'], ['acuerdos', 'escaneo']]">
                <form novalidate method="POST" action="{{ route('actas.store') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <x-form-step number="0" class="ui-form-grid">
                        <div>
                            <x-input-label for="fecha" value="Fecha de la reunión" />
                            <x-date-input id="fecha" name="fecha" :value="old('fecha', now()->toDateString())" class="mt-1" required />
                            <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tipo" value="Tipo de acta" />
                            <select id="tipo" name="tipo" class="mt-1 block w-full" required>
                                <option value="">Seleccionar</option>
                                @foreach (\App\Models\Acta::TIPOS as $tipo => $nombre)
                                    <option value="{{ $tipo }}" @selected(old('tipo') === $tipo)>{{ $nombre }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Las actas de asamblea son visibles para socios activos y dirigentes. Las de directiva son exclusivas de presidente, secretario y tesorero.</p>
                    </x-form-step>
                    <x-form-step number="1" class="ui-form-grid">
                        <div class="sm:col-span-2">
                            <x-input-label for="acuerdos" value="Acuerdos" />
                            <textarea id="acuerdos" name="acuerdos" rows="8" class="mt-1 block w-full" required>{{ old('acuerdos') }}</textarea>
                            <x-input-error :messages="$errors->get('acuerdos')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="escaneo" value="Escaneo opcional (PDF, JPG o PNG, máx. 5 MB)" />
                            <x-file-input id="escaneo" name="escaneo" accept=".pdf,.jpg,.jpeg,.png" />
                            <x-input-error :messages="$errors->get('escaneo')" class="mt-2" />
                            @if ($errors->any())
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Si habías adjuntado un escaneo, vuelve a seleccionarlo.</p>
                            @endif
                        </div>
                    </x-form-step>
                    <x-wizard-actions :cancel="route('actas.index')" />
                </form>
            </x-form-wizard>
        </div>
    </div>
</x-app-layout>
