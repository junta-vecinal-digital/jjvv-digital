@props(['id', 'columns', 'label' => 'Ver detalles'])

<tbody x-data="{ abierto: @js(old('operacion') === $id) }">
    <tr>
        {{ $summary }}
        <td><button type="button" class="ui-link whitespace-nowrap" x-ref="abrir" @click="abierto = !abierto" :aria-expanded="abierto" aria-controls="detalle-{{ $id }}"><span x-text="abierto ? 'Ocultar detalles' : @js($label)">{{ $label }}</span><span aria-hidden="true" x-text="abierto ? '▴' : '▾'">▾</span></button></td>
    </tr>
    <tr id="detalle-{{ $id }}" x-show="abierto" x-cloak class="ui-detail-row">
        <td colspan="{{ $columns }}">
            <div class="ui-detail-content space-y-6">
                {{ $slot }}
                <button type="button" class="ui-link" @click="abierto = false; $refs.abrir.focus()">Cerrar detalles</button>
            </div>
        </td>
    </tr>
</tbody>
