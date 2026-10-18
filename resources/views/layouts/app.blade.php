<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'JJVV Digital') }}</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <script>
            try {
                document.documentElement.classList.toggle('dark', localStorage.getItem('theme') === 'dark');
            } catch (_) {}
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-gray-50 font-sans text-gray-700 antialiased dark:bg-gray-900 dark:text-gray-300">
        <div x-data="appShell" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">
            @include('layouts.navigation')
            <div class="min-w-0 xl:ml-[290px]">
                <header class="relative z-20 flex min-h-20 items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <button x-ref="menuButton" type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="sidebar" aria-label="Abrir menú" class="rounded-lg border border-gray-200 p-2.5 xl:hidden dark:border-gray-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        <span class="text-sm font-medium text-gray-500">Gestión de la junta de vecinos</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <button type="button" @click="toggleTheme()" :aria-pressed="dark" aria-label="Activar o desactivar modo oscuro" class="flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.5 13A8.5 8.5 0 0 1 11 3.5 8.5 8.5 0 1 0 20.5 13Z" /></svg>
                        </button>
                        <x-dropdown align="right">
                            <x-slot name="trigger">
                                <button type="button" class="flex max-w-48 items-center gap-2 rounded-lg px-3 py-2 text-left hover:bg-gray-50 dark:hover:bg-gray-800" :aria-expanded="open">
                                    <span class="min-w-0"><span class="block truncate text-sm font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</span><span class="block text-xs text-gray-500">{{ Auth::user()->nombreRol() }}</span></span>
                                    <span aria-hidden="true">&#9662;</span>
                                </button>
                            </x-slot>
                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">Mi perfil</x-dropdown-link>
                                @if (Auth::user()->esDirigente())
                                    <x-dropdown-link :href="route('socios.create')">Mi ficha de socio</x-dropdown-link>
                                    @if (Auth::user()->esSocioActivo())
                                        <x-dropdown-link :href="route('certificados.index')">Mis certificados</x-dropdown-link>
                                    @endif
                                @endif
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">Cerrar sesión</button>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </header>
                @isset($header)
                    <div class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">{{ $header }}</div>
                @endisset
                <main id="main-content" class="app-content">
                    @if (Auth::user()->esDirigente() && ! Auth::user()->esSocioActivo())
                        <div role="status" class="mx-auto max-w-7xl px-4 pt-6 sm:px-6 lg:px-8">
                            <div class="ui-panel p-4">Completa y valida tu ficha de socio para realizar trámites personales. Conservas los permisos de tu cargo. <a class="font-semibold underline" href="{{ route('socios.create') }}">Revisar mi inscripción</a>. Otro presidente o secretario deberá aprobarla.</div>
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
