@props(['narrow' => false])
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>JJVV Digital</title>@vite(['resources/css/app.css','resources/js/app.js'])</head>
<body class="app-shell antialiased">
<div class="mx-auto max-w-5xl px-4 py-6 sm:px-8 sm:py-10">
<header class="mb-8 flex flex-wrap items-center justify-between gap-4 border-b border-gray-200 pb-5">
<a class="text-lg font-semibold tracking-tight" href="{{ url('/') }}">JJVV Digital</a>
<nav class="flex flex-wrap gap-4 text-sm" aria-label="Navegación pública">@auth<a class="ui-link" href="{{ route('dashboard') }}">Volver a gestión</a>
@else
<a class="ui-link" href="{{ route('login') }}">Iniciar sesión</a>
<a class="ui-link" href="{{ route('register') }}">Registrarme</a>@endauth @unless(request()->is('/'))<a class="ui-link" href="{{ url('/') }}">Volver a la portada</a>
@endunless
</nav>
</header>
<main class="app-content {{ $narrow ? 'mx-auto max-w-lg' : 'space-y-8' }}">{{ $slot }}</main>
</div>
</body>
</html>
