<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>JJVV Digital</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="app-shell antialiased">
        <div x-data="appShell" @keydown.escape.window="sidebarOpen = false" class="min-h-screen">
            @include('layouts.navigation')
            <div class="min-w-0 xl:ml-[220px]">
                <header class="relative z-20 flex min-h-20 items-center justify-between gap-4 border-b border-gray-200 bg-white px-4 sm:px-6 dark:border-gray-800 dark:bg-gray-900">
                    <div class="flex items-center gap-3">
                        <button x-ref="menuButton" type="button" @click="sidebarOpen = !sidebarOpen" :aria-expanded="sidebarOpen" aria-controls="sidebar" aria-label="Abrir menú" class="rounded-lg border border-gray-200 p-2.5 xl:hidden dark:border-gray-700">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>
                        <span class="text-sm font-medium text-gray-500">Gestión de la junta de vecinos</span>
                    </div>
                    <span class="text-xs text-gray-500">{{ Auth::user()->nombreRol() }}</span>
                </header>
                @include('layouts.ubicacion')
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
