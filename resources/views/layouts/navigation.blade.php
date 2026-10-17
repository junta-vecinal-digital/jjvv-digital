{{-- Navegación adaptada de TailAdmin para los permisos de JJVV Digital. --}}
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
        <x-sidebar-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
            {{ __('Panel') }}
        </x-sidebar-link>
                            @if (Auth::user()->hasRole('vecino'))
            <x-sidebar-link :href="route('socios.create')" :active="request()->routeIs('socios.create')">
                Mi solicitud de socio
            </x-sidebar-link>
        @endif

        @if (Auth::user()->esSocioActivo())
            <x-sidebar-link :href="route('certificados.index')" :active="request()->routeIs('certificados.*')">
                Mis certificados
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('presidente', 'secretario'))
            <x-sidebar-link :href="route('socios.index')" :active="request()->routeIs('socios.*')">
                Socios
            </x-sidebar-link>
            <x-sidebar-link :href="route('agenda.index')" :active="request()->routeIs('agenda.*')">
                Agenda de firmas
            </x-sidebar-link>
            <x-sidebar-link :href="route('admin.certificados.index')" :active="request()->routeIs('admin.certificados.*')">
                Certificados
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('presidente', 'secretario'))
            <x-sidebar-link :href="route('grupos.index')" :active="request()->routeIs('grupos.*')">
                Grupos
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('presidente', 'secretario') || Auth::user()->esSocioActivo())
            <x-sidebar-link :href="route('calendario.index')" :active="request()->routeIs('calendario.*')">
                Calendario
            </x-sidebar-link>
            <x-sidebar-link :href="route('arriendos.index')" :active="request()->routeIs('arriendos.*')">
                Arriendos
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('presidente', 'secretario'))
            <x-sidebar-link :href="route('parametros.index')" :active="request()->routeIs('parametros.*')">
                Parámetros
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('presidente', 'tesorero'))
            <x-sidebar-link :href="route('finanzas.arriendos.index')" :active="request()->routeIs('finanzas.arriendos.*')">
                Arriendos y garantías
            </x-sidebar-link>
            <x-sidebar-link :href="route('ingresos.index')" :active="request()->routeIs('ingresos.*')">
                Ingresos
            </x-sidebar-link>
            <x-sidebar-link :href="route('egresos.index')" :active="request()->routeIs('egresos.*')">
                Egresos
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('presidente'))
            <x-sidebar-link :href="route('auditoria.index')" :active="request()->routeIs('auditoria.*')">
                Auditoría
            </x-sidebar-link>
        @endif

        @if (Auth::user()->hasRole('admin'))
            <x-sidebar-link :href="route('usuarios.index')" :active="request()->routeIs('usuarios.*')">
                Usuarios
            </x-sidebar-link>
        @endif

    </nav>
    <div class="border-t border-gray-200 px-6 py-4 text-xs text-gray-500 dark:border-gray-800">Junta de vecinos · Gestión comunitaria</div>
</aside>
