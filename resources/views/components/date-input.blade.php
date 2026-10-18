@props(['id', 'name', 'value' => '', 'min' => '', 'max' => '', 'required' => false, 'disabled' => false])

<span class="relative inline-block max-w-full" x-data="fechaEspanol(@js((string) $value), @js((string) $min), @js((string) $max))" @keydown.escape.stop="abierto = false" @click.outside="abierto = false">
    <input type="hidden" name="{{ $name }}" :value="iso" @disabled($disabled) {{ $attributes->only(['x-bind:disabled', ':disabled']) }}>
    <span class="flex items-center gap-1">
        <input type="text" id="{{ $id }}" x-ref="campo" x-model="texto" value="{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $value) ? implode('/', array_reverse(explode('-', $value))) : $value }}" data-resumen="fecha" inputmode="numeric" placeholder="dd/mm/aaaa" maxlength="10" autocomplete="off"
            @required($required) @disabled($disabled) @input="actualizar()" @blur="validar()" @validar-fecha="validar()" {{ $attributes->except(['type', 'value', 'name', 'min', 'max'])->class(['ui-input min-w-0 w-40']) }}>
        <button type="button" class="ui-button ui-button-secondary shrink-0 px-2" @click="abierto = !abierto" :disabled="$refs.campo.disabled" :aria-expanded="abierto" aria-label="Elegir fecha">▦</button>
    </span>
    <span x-cloak x-show="abierto" class="mt-1 block w-80 max-w-full rounded-xl border border-gray-300 bg-white p-3 shadow-lg dark:border-gray-700 dark:bg-gray-900" role="group" aria-label="Calendario en español">
        <span class="flex items-center justify-between gap-2 mb-3">
            <button type="button" class="p-2" @click="moverMes(-1)" aria-label="Mes anterior">←</button>
            <select x-model.number="mes" aria-label="Mes" class="ui-input min-w-0 flex-1">
                @foreach (['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'] as $numero => $mes)
                    <option value="{{ $numero }}">{{ $mes }}</option>
                @endforeach
            </select>
            <select x-model.number="ano" aria-label="Año" class="ui-input w-24 shrink-0">
                <template x-for="opcionAno in anos" :key="opcionAno"><option :value="opcionAno" :selected="opcionAno === ano" x-text="opcionAno"></option></template>
            </select>
            <button type="button" class="p-2" @click="moverMes(1)" aria-label="Mes siguiente">→</button>
        </span>
        <span class="grid grid-cols-7 gap-1 text-center text-sm">
            @foreach (['Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá', 'Do'] as $dia)<span class="font-medium">{{ $dia }}</span>@endforeach
            <template x-for="(dia, indice) in dias" :key="indice">
                <button type="button" class="rounded p-1 hover:bg-brand-50 disabled:opacity-30 dark:hover:bg-gray-800" :class="dia.iso === iso ? 'bg-brand-50 text-brand-600' : ''"
                    :disabled="!dia.numero || (minimo && dia.iso < minimo) || (maximo && dia.iso > maximo)" :aria-label="dia.iso ? mostrar(dia.iso) : 'Fuera del mes'" @click="elegir(dia.iso)" x-text="dia.numero || ''"></button>
            </template>
        </span>
        <button type="button" class="mt-2 underline text-sm" @click="elegir('')">Borrar fecha</button>
    </span>
</span>
