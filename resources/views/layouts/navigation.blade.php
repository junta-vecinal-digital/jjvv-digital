{{-- Navegación adaptada de TailAdmin para los permisos de JJVV Digital. --}}
@php
    $directiva = Auth::user()->hasRole('presidente', 'secretario');
    $socioActivo = Auth::user()->esSocioActivo();
    $enFinanzas = request()->routeIs('ingresos.*', 'egresos.*', 'finanzas.*')
        || (request()->routeIs('arriendos.show') && request('desde') === 'finanzas' && Auth::user()->hasRole('presidente', 'tesorero'));
    $enReservas = request()->routeIs('reservas.*', 'insumos.*')
        || (request()->routeIs('arriendos.*') && ! $enFinanzas);
@endphp
<div x-cloak x-show="sidebarOpen" class="fixed inset-0 z-30 bg-gray-900/50 xl:hidden" @click="sidebarOpen = false" aria-hidden="true"></div>
<aside x-cloak id="sidebar" aria-label="Menú principal"
    class="fixed inset-y-0 left-0 z-40 flex w-[290px] flex-col border-r border-gray-200 bg-white transition-transform duration-200 xl:translate-x-0 dark:border-gray-800 dark:bg-gray-900"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
    :inert="!sidebarOpen && !desktop">
    <div class="flex h-20 shrink-0 items-center justify-between px-6">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 font-semibold text-gray-900 dark:text-white">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-brand-500 text-sm text-white">JV</span>
            <span>JJVV Digital</span>
        </a>
        <button type="button" @click="sidebarOpen = false; $refs.menuButton.focus()" class="rounded-lg p-2 text-gray-500 xl:hidden" aria-label="Cerrar menú">&#10005;</button>
    </div>
    <nav class="flex-1 space-y-1 overflow-y-auto px-5 pb-6">
        <p class="px-3 pb-3 pt-5 text-xs font-medium uppercase tracking-wider text-gray-400">Menú principal</p>
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Panel</x-sidebar-link>
        @if ($directiva)
            <x-sidebar-link :href="route('socios.index')" :active="request()->routeIs('socios.*') && ! request()->routeIs('socios.create')">
                Gestión de socios
                @if ($pendientesMenu['socios'])<x-pendiente-dot data-pendiente="socios" />@endif
            </x-sidebar-link>
            <x-sidebar-link :href="route('admin.certificados.index')" :active="request()->routeIs('admin.certificados.*')">Gestión de certificados @if ($pendientesMenu['certificados'])<x-pendiente-dot data-pendiente="certificados" />@endif</x-sidebar-link>
            <x-sidebar-link :href="route('grupos.index')" :active="request()->routeIs('grupos.*')">Gestión de agrupaciones</x-sidebar-link>
        @endif
        @if (Auth::user()->hasRole('vecino'))
            <x-sidebar-link :href="route('socios.create')" :active="request()->routeIs('socios.create')">Mi ficha de socio</x-sidebar-link>
        @endif
        @if ($directiva || $socioActivo)
            <x-sidebar-link :href="route('calendario.index')" :active="request()->routeIs('calendario.*')">Calendario</x-sidebar-link>
            <x-sidebar-group label="Reservas" :active="$enReservas" :pending="$pendientesMenu['arriendos'] || $pendientesMenu['insumos'] || $pendientesMenu['prestamos']">
                <x-sidebar-link :href="route($directiva ? 'reservas.index' : 'arriendos.index')" :active="request()->routeIs('reservas.*') || (request()->routeIs('arriendos.*') && ! $enFinanzas)">Reservas de sede @if ($pendientesMenu['arriendos'])<x-pendiente-dot data-pendiente="arriendos" />@endif</x-sidebar-link>
                <x-sidebar-link :href="route('insumos.index')" :active="request()->routeIs('insumos.*')">{{ $directiva ? 'Solicitudes de insumos' : 'Mis solicitudes de insumos' }} @if ($pendientesMenu['insumos'])<x-pendiente-dot data-pendiente="insumos" />@elseif ($pendientesMenu['prestamos'])<x-pendiente-dot data-pendiente="prestamos" />@endif</x-sidebar-link>
            </x-sidebar-group>
        @endif
        @if ($directiva)
            <x-sidebar-group label="Inventario" :active="request()->routeIs('inventario.*')">
                <x-sidebar-link :href="route('inventario.index')" :active="request()->routeIs('inventario.index')">Registrar insumo</x-sidebar-link>
                <x-sidebar-link :href="route('inventario.prestamos.index')" :active="request()->routeIs('inventario.prestamos.*')">Historial de préstamos</x-sidebar-link>
            </x-sidebar-group>
            <x-sidebar-group label="Horarios de atención" :active="request()->routeIs('atencion.*', 'agenda.*')">
                <x-sidebar-link :href="route('atencion.index')" :active="request()->routeIs('atencion.*')">Atención al público</x-sidebar-link>
                <x-sidebar-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')">Agenda de firmas</x-sidebar-link>
            </x-sidebar-group>
        @endif
        @if ($socioActivo && ! Auth::user()->esDirigente())
            <x-sidebar-link :href="route('certificados.index')" :active="request()->routeIs('certificados.*')">Mis certificados</x-sidebar-link>
        @endif
        @if (Auth::user()->hasRole('presidente', 'tesorero'))
            <x-sidebar-group label="Finanzas" :active="$enFinanzas">
                <x-sidebar-link :href="route('finanzas.pendientes.index')" :active="request()->routeIs('finanzas.pendientes.*')">Ingresos por aprobar</x-sidebar-link>
                <x-sidebar-link :href="route('ingresos.index')" :active="request()->routeIs('ingresos.*')">Ingresos</x-sidebar-link>
                <x-sidebar-link :href="route('egresos.index')" :active="request()->routeIs('egresos.*')">Egresos</x-sidebar-link>
                <x-sidebar-link :href="route('finanzas.arriendos.index')" :active="request()->routeIs('finanzas.arriendos.*') || (request()->routeIs('arriendos.show') && $enFinanzas)">Garantías de sede</x-sidebar-link>
                <x-sidebar-link :href="route('finanzas.insumos.index')" :active="request()->routeIs('finanzas.insumos.*')">Garantías de insumos</x-sidebar-link>
            </x-sidebar-group>
        @endif
        @if (Auth::user()->esDirigente() || $socioActivo)
            <x-sidebar-link :href="route('actas.index')" :active="request()->routeIs('actas.*')">Actas</x-sidebar-link>
        @endif
        <x-sidebar-link :href="route('proyectos.index')" :active="request()->routeIs('proyectos.*')">Proyectos</x-sidebar-link>
        @if (Auth::user()->esDirigente())
            <x-sidebar-link :href="route('reportes.index')" :active="request()->routeIs('reportes.*')">Reportes</x-sidebar-link>
        @endif
        @if (Auth::user()->hasRole('admin'))
            <x-sidebar-link :href="route('parametros.index')" :active="request()->routeIs('parametros.*')">Parámetros</x-sidebar-link>
            <x-sidebar-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">Usuarios</x-sidebar-link>
        @endif
        @if (Auth::user()->hasRole('presidente'))
            <x-sidebar-link :href="route('auditoria.index')" :active="request()->routeIs('auditoria.*')">Auditoría</x-sidebar-link>
        @endif
    </nav>
    <div class="border-t border-gray-200 px-6 py-4 text-xs text-gray-500 dark:border-gray-800">Junta de vecinos · Gestión comunitaria</div>
</aside>
