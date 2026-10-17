<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white leading-tight">Parámetros del sistema</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="ui-panel p-6 ">
                @if (session('status') === 'parametros-guardados')
                    <p class="mb-4 text-green-700 dark:text-green-400">Parámetros guardados.</p>
                @endif

                <form method="POST" action="{{ route('parametros.update') }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    @foreach ($parametros as $parametro)
                        <div>
                            <x-input-label for="valores_{{ $parametro->clave }}" :value="$parametro->etiqueta" />
                            <x-text-input
                                id="valores_{{ $parametro->clave }}"
                                name="valores[{{ $parametro->clave }}]"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('valores.'.$parametro->clave, $parametro->valor)"
                            />
                            <x-input-error :messages="$errors->get('valores.'.$parametro->clave)" class="mt-2" />
                        </div>
                    @endforeach

                    <x-primary-button>Guardar</x-primary-button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>