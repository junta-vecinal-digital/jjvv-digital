<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Horarios de atención al público</h1>
    </x-slot>
    <div class="mx-auto max-w-4xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        @if (session('status'))
            <p role="status" class="rounded-lg bg-green-100 p-4 text-sm text-green-800 dark:bg-green-900/40 dark:text-green-200">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div role="alert" class="rounded-lg bg-red-100 p-4 text-sm text-red-800 dark:bg-red-900/40 dark:text-red-200">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <section class="ui-panel space-y-4 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Publicar un tramo semanal</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Se repite cada semana y se muestra en la <a href="{{ url('/') }}" class="underline">portada pública</a>. Puedes agregar varios tramos por día. Estos horarios informan la atención de ventanilla; no crean citas de firma ni reservas de la sede.</p>
            <form method="POST" action="{{ route('atencion.store') }}" class="space-y-4">
                @csrf
                @include('atencion._form', ['horario' => null])
                <button class="ui-button ui-button-primary">Publicar horario</button>
            </form>
        </section>
        <section class="ui-panel space-y-4 p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Horarios publicados</h2>
            @forelse ($horarios as $horario)
                <div class="space-y-3 border-t border-gray-200 pt-4 dark:border-gray-800">
                    <p class="font-medium text-gray-900 dark:text-white">{{ \App\Models\HorarioAtencion::DIAS[$horario->dia_semana] }} · {{ substr($horario->hora_inicio, 0, 5) }} a {{ substr($horario->hora_fin, 0, 5) }}</p>
                    <details @if ($errors->any() && (string) old('_horario') === (string) $horario->id) open @endif>
                        <summary class="cursor-pointer text-sm text-brand-500">Editar horario</summary>
                        <form method="POST" action="{{ route('atencion.update', $horario) }}" class="mt-4 space-y-4">
                            @csrf @method('PUT')
                            @include('atencion._form', ['horario' => $horario])
                            <button class="ui-button ui-button-primary">Guardar cambios</button>
                        </form>
                    </details>
                    <form method="POST" action="{{ route('atencion.destroy', $horario) }}">
                        @csrf @method('DELETE')
                        <button class="ui-button ui-button-secondary">Retirar horario</button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500 dark:text-gray-400">Aún no hay horarios publicados.</p>
            @endforelse
        </section>
        <p class="text-sm text-gray-500 dark:text-gray-400">Para habilitar horas de firma, utiliza la <a href="{{ route('agenda.index') }}" class="underline text-brand-500">Agenda de firmas</a>. Sus horarios se gestionan por separado.</p>
    </div>
</x-app-layout>
