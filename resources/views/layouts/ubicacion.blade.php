@php
    $rutaActual = request()->route()?->getName();
    $gestiona = auth()->user()->hasRole('presidente', 'secretario');
    $secciones = [
        'socios.archivo' => ['socios.archivo', 'Archivo de socios'],
        'socios.create' => ['socios.create', 'Mi ficha de socio'],
        'socios.*' => ['socios.index', 'Gestión de socios'],
        'admin.certificados.*' => ['admin.certificados.index', 'Gestión de certificados'],
        'certificados.*' => ['certificados.index', 'Mis certificados'],
        'grupos.*' => ['grupos.index', 'Gestión de agrupaciones'],
        'calendario.*' => ['calendario.index', 'Calendario'],
        'reservas.*' => ['reservas.index', 'Reservas de sede'],
        'insumos.*' => ['insumos.index', 'Solicitudes de insumos'],
        'inventario.prestamos.*' => ['inventario.prestamos.index', 'Historial de préstamos'],
        'inventario.*' => ['inventario.index', 'Inventario'],
        'ingresos.*' => ['ingresos.index', 'Ingresos'],
        'egresos.*' => ['egresos.index', 'Egresos'],
        'finanzas.pendientes.*' => ['ingresos.index', 'Ingresos'],
        'finanzas.arriendos.*' => ['finanzas.arriendos.index', 'Registro garantías'],
        'finanzas.insumos.*' => ['finanzas.arriendos.index', 'Registro garantías'],
        'actas.*' => ['actas.index', 'Actas'],
        'proyectos.*' => ['proyectos.index', 'Proyectos'],
        'reportes.*' => ['reportes.index', 'Reportes'],
        'agenda.*' => ['agenda.index', 'Agenda de firmas'],
        'atencion.*' => ['atencion.index', 'Atención al público'],
        'citas.*' => ['citas.index', 'Mi cita de firma'],
        'usuarios.*' => ['usuarios.index', 'Usuarios'],
        'parametros.*' => ['parametros.index', 'Parámetros'],
        'auditoria.*' => ['auditoria.index', 'Auditoría'],
        'profile.*' => ['profile.edit', 'Mi perfil'],
    ];
    $seccion = null;
    foreach ($secciones as $patron => $datos) {
        if (request()->routeIs($patron)) { $seccion = $datos; break; }
    }
    if (request()->routeIs('arriendos.*')) {
        $desdeFinanzas = request('desde') === 'finanzas' && auth()->user()->hasRole('presidente', 'tesorero');
        $soloFinanzas = ! $gestiona && ! auth()->user()->esSocioActivo();
        $seccion = $desdeFinanzas || $soloFinanzas ? ['finanzas.arriendos.index', 'Registro garantías']
            : [$gestiona ? 'reservas.index' : 'arriendos.index', $gestiona ? 'Reservas de sede' : 'Mis reservas de sede'];
    }
@endphp
@if ($rutaActual !== 'dashboard' && $seccion)
    <nav aria-label="Ubicación actual" class="mx-auto flex max-w-7xl flex-wrap items-center gap-2 px-4 pt-5 text-sm text-gray-500 sm:px-6 lg:px-8">
        <a href="{{ route('dashboard') }}" class="underline hover:text-brand-500">Panel</a>
        <span aria-hidden="true">/</span>
        @if ($rutaActual === $seccion[0])
            <span aria-current="page">{{ $seccion[1] }}</span>
        @else
            <a href="{{ route($seccion[0]) }}" class="underline hover:text-brand-500">{{ $seccion[1] }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ match (last(explode('.', $rutaActual))) { 'create', 'crear' => 'Nuevo registro', 'edit', 'editar' => 'Editar', 'show' => 'Detalle', default => 'Consulta' } }}</span>
        @endif
    </nav>
@endif
