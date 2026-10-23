@php
    $directiva = Auth::user()->hasRole('presidente', 'secretario');
    $socioActivo = Auth::user()->esSocioActivo();
    $enFinanzas = request()->routeIs('ingresos.*', 'egresos.*', 'finanzas.*') || (request()->routeIs('arriendos.show') && request('desde') === 'finanzas');
    $enReservas = request()->routeIs('reservas.*', 'insumos.*') || (request()->routeIs('arriendos.*') && ! $enFinanzas);
@endphp
<div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-30 bg-gray-900/50 xl:hidden" @click="sidebarOpen = false" aria-hidden="true">
</div>
<aside x-cloak id="sidebar" aria-label="Menú principal" class="fixed inset-y-0 left-0 z-40 flex w-[220px] flex-col border-r border-gray-200 bg-white transition-transform xl:translate-x-0" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" :inert="!sidebarOpen && !desktop">
<div class="flex h-20 shrink-0 items-center justify-between px-5">
<a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight">JJVV Digital</a>
<button type="button" @click="sidebarOpen = false; $refs.menuButton.focus()" class="p-2 xl:hidden" aria-label="Cerrar menú">✕</button>
</div>
<nav class="flex-1 space-y-1 overflow-y-auto px-3 pb-6">
<details class="group mb-4 border-b border-gray-200 pb-4" @if(request()->routeIs('profile.*', 'socios.create', 'certificados.*')) open @endif>
<summary class="menu-item cursor-pointer"><span class="text-black transition-transform group-open:rotate-90" aria-hidden="true">▶</span>Mi cuenta
</summary>
<p class="px-4 py-2 text-xs text-gray-500">{{ Auth::user()->name }} · {{ Auth::user()->nombreRol() }}</p>
<x-sidebar-link :href="route('profile.edit')" :active="request()->routeIs('profile.*')">Mi perfil</x-sidebar-link>
            @if(Auth::user()->esDirigente())
                <x-sidebar-link :href="route('socios.create')" :active="request()->routeIs('socios.create')">Mi ficha de socio</x-sidebar-link>
                @if($socioActivo)<x-sidebar-link :href="route('certificados.index')" :active="request()->routeIs('certificados.*')">Mis certificados</x-sidebar-link>
@endif
            @endif
            <form method="POST" action="{{ route('logout') }}">
@csrf<button class="menu-item menu-item-inactive">Cerrar sesión</button>
</form>
</details>
<x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Panel de inicio</x-sidebar-link>
        @if($directiva)
            <p class="ui-menu-label">Atención a vecinos</p>
<x-sidebar-link :href="route('socios.index')" :active="request()->routeIs('socios.*') && !request()->routeIs('socios.create', 'socios.archivo') && request('seccion') !== 'archivo'">Gestión de socios @if($pendientesMenu['socios'])<x-pendiente-dot />
@endif
</x-sidebar-link>
<x-sidebar-link :href="route('admin.certificados.index')" :active="request()->routeIs('admin.certificados.*')">Gestión de certificados @if($pendientesMenu['certificados'])<x-pendiente-dot />
@endif
</x-sidebar-link>
<x-sidebar-group label="Horarios de atención" :active="request()->routeIs('atencion.*', 'agenda.*')">
<x-sidebar-link :href="route('atencion.index')" :active="request()->routeIs('atencion.*')">Atención al público</x-sidebar-link>
<x-sidebar-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')">Agenda de firmas</x-sidebar-link>
</x-sidebar-group>
        @endif
        @if($directiva || $socioActivo)
            <p class="ui-menu-label">Sede e insumos</p>
<x-sidebar-link :href="route('calendario.index')" :active="request()->routeIs('calendario.*')">Calendario</x-sidebar-link>
<x-sidebar-link :href="route($directiva ? 'reservas.index' : 'arriendos.index')" :active="$enReservas">Reservas @if($pendientesMenu['arriendos'] || $pendientesMenu['insumos'] || $pendientesMenu['prestamos'])<x-pendiente-dot />
@endif
</x-sidebar-link>
        @endif
        @if($directiva)
            <x-sidebar-group label="Inventario" :active="request()->routeIs('inventario.*')">
<x-sidebar-link :href="route('inventario.index')" :active="request()->routeIs('inventario.index')">Registrar insumo</x-sidebar-link>
<x-sidebar-link :href="route('inventario.prestamos.index')" :active="request()->routeIs('inventario.prestamos.*')">Historial de préstamos</x-sidebar-link>
</x-sidebar-group>
<x-sidebar-link :href="route('grupos.index')" :active="request()->routeIs('grupos.*')">Gestión de agrupaciones</x-sidebar-link>
        @endif
        @if(Auth::user()->hasRole('presidente', 'tesorero'))
            <p class="ui-menu-label">Gestión financiera</p>
<x-sidebar-group label="Finanzas" :active="$enFinanzas" :pending="$pendientesMenu['finanzas']">
<x-sidebar-link :href="route('ingresos.index')" :active="request()->routeIs('ingresos.*', 'egresos.*', 'finanzas.pendientes.*')">Ingresos y egresos</x-sidebar-link>
<x-sidebar-link :href="route('finanzas.arriendos.index')" :active="$enFinanzas && !request()->routeIs('ingresos.*', 'egresos.*', 'finanzas.pendientes.*')">Registro garantías</x-sidebar-link>
</x-sidebar-group>
        @endif
        @if(Auth::user()->hasRole('admin'))
            <p class="ui-menu-label">Administración</p>
<x-sidebar-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">Usuarios</x-sidebar-link>
<x-sidebar-link :href="route('parametros.index')" :active="request()->routeIs('parametros.*')">Parámetros y catálogos</x-sidebar-link>
        @endif
        <p class="ui-menu-label">Consulta y transparencia</p>
        @if($directiva)<x-sidebar-link :href="route('socios.archivo')" :active="request()->routeIs('socios.archivo') || request('seccion') === 'archivo'">Archivo de socios</x-sidebar-link>
@endif
        @if(Auth::user()->hasRole('vecino'))<x-sidebar-link :href="route('socios.create')" :active="request()->routeIs('socios.create')">Mi ficha de socio</x-sidebar-link>
@endif
        @if($socioActivo && !Auth::user()->esDirigente())<x-sidebar-link :href="route('certificados.index')" :active="request()->routeIs('certificados.*')">Mis certificados</x-sidebar-link>
@endif
        @if(Auth::user()->esDirigente() || $socioActivo)<x-sidebar-link :href="route('actas.index')" :active="request()->routeIs('actas.*')">Actas</x-sidebar-link>
@endif
        <x-sidebar-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.*')">Proyectos</x-sidebar-link>
        @if(Auth::user()->esDirigente())<x-sidebar-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')">Reportes</x-sidebar-link>
@endif
        <x-sidebar-link :href="url('/')">Portada pública</x-sidebar-link>
        @if(Auth::user()->hasRole('presidente'))<div class="mt-5 border-t border-gray-200 pt-4">
<x-sidebar-link :href="route('auditoria.index')" :active="request()->routeIs('auditoria.*')">Auditoría</x-sidebar-link>
</div>
@endif
    </nav>
</aside>
