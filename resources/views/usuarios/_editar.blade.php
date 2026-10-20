@php
    $operacion = 'usuario_'.$usuario->id;
    $valor = fn ($campo, $defecto = null) => old('operacion') === $operacion ? old($campo, $defecto) : $defecto;
    $errors = old('operacion') === $operacion ? $errors : new \Illuminate\Support\ViewErrorBag;
@endphp
<form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="space-y-6" x-data="claveCuenta">@csrf @method('PATCH')<input type="hidden" name="operacion" value="{{ $operacion }}"><input type="hidden" name="accion" value="editar">@include('usuarios._form')<x-primary-button>Guardar usuario</x-primary-button></form>
            @if ($usuario->esDirigente() && $usuario->socio?->estado === 'activo')
                <details class="border-t border-gray-200 pt-4" @if(old('operacion') === 'usuario_'.$usuario->id && old('accion') === 'baja') open @endif>
                    <summary>Dar de baja la ficha de socio del dirigente</summary>
                    <p class="text-sm">Retira sus accesos personales de socio y conserva los permisos de su cargo. No elimina su cuenta.</p>
                    <form method="POST" action="{{ route('usuarios.baja', $usuario) }}" class="space-y-4" onsubmit="return confirm('¿Dar de baja la ficha de este dirigente?');">
                        @csrf
<input type="hidden" name="operacion" value="usuario_{{ $usuario->id }}"><input type="hidden" name="accion" value="baja">
                        <x-input-label for="motivo_baja_usuario_{{ $usuario->id }}" value="Motivo de baja" />
                        <textarea id="motivo_baja_usuario_{{ $usuario->id }}" name="motivo_baja" maxlength="500" required class="w-full">{{ $valor('motivo_baja') }}</textarea>
                        <x-input-error :messages="$errors->get('motivo_baja')" />
                        <x-input-label for="fecha_baja_usuario_{{ $usuario->id }}" value="Fecha de baja" />
                        <x-date-input id="fecha_baja_usuario_{{ $usuario->id }}" name="fecha_baja" :value="$valor('fecha_baja', now()->toDateString())" :max="now()->toDateString()" required />
                        <x-input-error :messages="$errors->get('fecha_baja')" />
                        <x-danger-button>Dar de baja ficha</x-danger-button>
                    </form>
                </details>
            @endif
            @if (! $usuario->is(auth()->user()))
                <details class="border-t border-gray-200 pt-4" @if(old('operacion') === 'usuario_'.$usuario->id && old('accion') === 'eliminar') open @endif>
                    <summary>Eliminar cuenta de usuario</summary>
                    <p class="text-sm">La ficha de socio y los registros históricos se conservan. La cuenta pierde el acceso al sistema.</p>
                    <form method="POST" action="{{ route('usuarios.destroy', $usuario) }}" class="space-y-4" onsubmit="return confirm('¿Eliminar esta cuenta de usuario?');">
                        @csrf
<input type="hidden" name="operacion" value="usuario_{{ $usuario->id }}"><input type="hidden" name="accion" value="eliminar"> @method('DELETE')
                        <label class="flex items-center gap-2"><input type="checkbox" name="confirmar_eliminacion" value="1" required> Confirmo que deseo eliminar esta cuenta.</label>
                        <x-input-error :messages="$errors->get('confirmar_eliminacion')" />
                        <x-danger-button>Eliminar cuenta</x-danger-button>
                    </form>
                </details>
            @endif
