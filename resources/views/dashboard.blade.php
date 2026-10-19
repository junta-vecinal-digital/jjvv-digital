<x-app-layout>
    <x-slot name="header">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">Panel de inicio</h1>
    </x-slot>
    <div class="mx-auto max-w-7xl space-y-6 px-4 py-6 sm:px-6 lg:px-8">
        <section class="rounded-2xl border border-gray-200 bg-white p-5 sm:p-6 dark:border-gray-800 dark:bg-gray-900">
            <span class="inline-flex rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-600 dark:bg-brand-500/15 dark:text-brand-400">{{ Auth::user()->nombreRol() }}</span>
            <h2 class="mt-2 text-xl font-semibold text-gray-900 dark:text-white">Bienvenido, {{ Auth::user()->name }}</h2>
            <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Elige una gestión para comenzar. También puedes acceder a todos los apartados desde el menú lateral.</p>
        </section>
        @if (Auth::user()->hasRole('presidente', 'secretario'))
            @php
                $accesos = [
                    ['socios.index', 'Gestión de socios', 'Inscripciones, fichas y revisión de antecedentes.'],
                    ['admin.certificados.index', 'Gestión de certificados', 'Revisar solicitudes y emitir en ventanilla.'],
                    ['reservas.index', 'Reserva en ventanilla', 'Registrar y revisar el uso de la sede.'],
                    ['insumos.index', 'Solicitudes de insumos', 'Revisar solicitudes y gestionar su entrega.'],
                ];
            @endphp
            <section aria-labelledby="gestiones-frecuentes">
                <h2 id="gestiones-frecuentes" class="mb-3 text-lg font-semibold text-gray-900 dark:text-white">¿Qué necesitas hacer?</h2>
                <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    @foreach ($accesos as [$ruta, $titulo, $descripcion])
                        <a href="{{ route($ruta) }}" class="ui-panel flex flex-col p-5 transition-colors hover:border-brand-400 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-500">
                            <h3 class="font-semibold text-gray-900 dark:text-white">{{ $titulo }}</h3>
                            <p class="mb-4 mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $descripcion }}</p>
                            <span class="mt-auto text-sm font-medium text-brand-600 dark:text-brand-400">Abrir &rarr;</span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
        @if (Auth::user()->hasRole('presidente'))
            <div class="grid gap-6 md:grid-cols-2">
                <section class="ui-panel p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Socios pendientes de revisión</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Solicitudes más antiguas por revisar.</p>
                    <ul class="my-4 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        @forelse ($sociosPendientes as $pendiente)
                            <li class="py-3"><a href="{{ route('socios.show', $pendiente) }}" class="text-brand-500 underline">{{ $pendiente->nombre_completo }}</a></li>
                        @empty
                            <li class="py-3 text-gray-500 dark:text-gray-400">No hay solicitudes pendientes de revisión.</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('socios.index', ['estado' => 'pendiente']) }}" class="text-sm font-medium text-brand-500 underline">Ver todas las solicitudes</a>
                </section>
                <section class="ui-panel p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Certificados pendientes</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Solicitudes más antiguas por revisar.</p>
                    <ul class="my-4 divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        @forelse ($certificadosPendientes as $pendiente)
                            <li class="py-3"><a href="{{ route('admin.certificados.show', $pendiente) }}" class="text-brand-500 underline">{{ $pendiente->nombre_solicitante }} · {{ $pendiente->tipo === 'socio' ? 'Socio' : 'Residencia' }}</a></li>
                        @empty
                            <li class="py-3 text-gray-500 dark:text-gray-400">No hay certificados pendientes.</li>
                        @endforelse
                    </ul>
                    <a href="{{ route('admin.certificados.index', ['estado' => 'solicitado']) }}" class="text-sm font-medium text-brand-500 underline">Ver todas las solicitudes</a>
                </section>
            </div>
        @endif
        <div class="grid gap-6 md:grid-cols-2">
            @if (Auth::user()->hasRole('presidente', 'secretario') || Auth::user()->esSocioActivo())
                <a href="{{ route('calendario.index') }}" class="rounded-2xl border border-gray-200 bg-white p-6 transition-colors hover:border-brand-400 dark:border-gray-800 dark:bg-gray-900">
                    <span class="text-xs font-medium uppercase tracking-wider text-brand-500">Sede vecinal</span>
                    <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">Calendario de espacios</h2>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Consulta los horarios de uso de la sede.</p>
                    <span class="mt-5 inline-block text-sm font-medium text-brand-500">Ver calendario &rarr;</span>
                </a>
            @endif
            <a href="{{ route('profile.edit') }}" class="rounded-2xl border border-gray-200 bg-white p-6 transition-colors hover:border-brand-400 dark:border-gray-800 dark:bg-gray-900">
                <span class="text-xs font-medium uppercase tracking-wider text-brand-500">Tu cuenta</span>
                <h2 class="mt-3 text-lg font-semibold text-gray-900 dark:text-white">Mi perfil</h2>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Revisa tus datos de cuenta y actualiza tu contraseña.</p>
                <span class="mt-5 inline-block text-sm font-medium text-brand-500">Ir a mi perfil &rarr;</span>
            </a>
        </div>
    </div>
</x-app-layout>
