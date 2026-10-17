@php
    $prefijo = $horario?->id ?? 'nuevo';
    $recuperar = $errors->any() && (string) old('_horario', '') === (string) ($horario?->id ?? '');
@endphp
<input type="hidden" name="_horario" value="{{ $horario?->id }}">
<div class="grid gap-4 sm:grid-cols-3">
    <div>
        <x-input-label :for="'dia-'.$prefijo" value="Día de la semana" />
        <select id="dia-{{ $prefijo }}" name="dia_semana" class="mt-1 w-full" required>
            @foreach (\App\Models\HorarioAtencion::DIAS as $numero => $nombre)
                <option value="{{ $numero }}" @selected((string) ($recuperar ? old('dia_semana') : ($horario?->dia_semana ?? 1)) === (string) $numero)>{{ $nombre }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <x-input-label :for="'inicio-'.$prefijo" value="Desde" />
        <input id="inicio-{{ $prefijo }}" type="time" name="hora_inicio" class="mt-1 w-full" value="{{ $recuperar ? old('hora_inicio') : substr($horario?->hora_inicio ?? '', 0, 5) }}" required>
    </div>
    <div>
        <x-input-label :for="'fin-'.$prefijo" value="Hasta" />
        <input id="fin-{{ $prefijo }}" type="time" name="hora_fin" class="mt-1 w-full" value="{{ $recuperar ? old('hora_fin') : substr($horario?->hora_fin ?? '', 0, 5) }}" required>
    </div>
</div>
