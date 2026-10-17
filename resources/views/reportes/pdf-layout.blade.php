<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>@yield('titulo')</title>
    <style>
        @page { margin: 24px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #111; }
        h1 { font-size: 18px; margin-bottom: 8px; }
        h2 { font-size: 13px; margin-top: 20px; }
        p { line-height: 1.5; }
        table { width: 100%; border-collapse: collapse; margin: 12px 0; table-layout: fixed; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; vertical-align: top; overflow-wrap: break-word; }
        th { background: #eee; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .nota { font-size: 8px; color: #444; }
    </style>
</head>
<body>
    <h1>@yield('titulo')</h1>
    <p>{{ config('jjvv.nombre') }} · Generado el {{ $emitidoEn->format('d/m/Y H:i') }}</p>
    @yield('contenido')
</body>
</html>
