<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Socio;
use App\Models\Reserva;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Grupos y organizaciones de la sede (RFU-29, RFU-30, RFS-38 a RFS-40). */
class GrupoController extends Controller
{
    public function index()
    {
        return view('grupos.index', [
            'grupos' => Grupo::with('representante')->orderBy('nombre')->get(),
            'socios' => $this->sociosDisponibles(),
        ]);
    }

    public function store(Request $request)
    {
        $grupo = Grupo::create($this->validarGrupo($request));

        return redirect()->route('grupos.show', $grupo)->with('status', 'grupo-creado');
    }

    public function show(Grupo $grupo)
    {
        $bloques = Reserva::bloquesDeLaSede();

        return view('grupos.show', [
            'grupo' => $grupo->load([
                'representante',
                'horariosFijos' => fn ($q) => $q->orderBy('dia_semana')->orderBy('hora_inicio'),
            ]),
            'socios' => $this->sociosDisponibles(),
            'horas' => $bloques['inicios'],
            'horasFin' => $bloques['fines'],
        ]);
    }

    public function update(Request $request, Grupo $grupo)
    {
        $grupo->update($this->validarGrupo($request, $grupo));

        return redirect()->route('grupos.show', $grupo)->with('status', 'grupo-actualizado');
    }

    /** RFS-39 y RFS-40: el representante debe ser socio activo con cuenta; sus datos se copian al grupo. */
    public function asignarRepresentante(Request $request, Grupo $grupo)
    {
        $datos = $request->validate([
            'socio_id' => ['required', 'exists:socios,id'],
        ]);

        $socio = Socio::findOrFail($datos['socio_id']);

        if ($socio->estado !== 'activo' || $socio->user_id === null) {
            return back()->withErrors([
                'socio_id' => 'El representante debe ser socio activo con cuenta de usuario.',
            ]);
        }

        $grupo->update([
            'representante_id' => $socio->id,
            'contacto_nombre' => $socio->nombre_completo,
            'contacto_correo' => $socio->email,
            'contacto_telefono' => $socio->telefono,
            'contacto_direccion' => $socio->direccion,
        ]);

        return redirect()->route('grupos.show', $grupo)->with('status', 'representante-asignado');
    }

    /** RFS-40: editar el contacto del grupo sin alterar la ficha del representante. */
    public function actualizarContacto(Request $request, Grupo $grupo)
    {
        $datos = $request->validate([
            'contacto_nombre' => ['nullable', 'string', 'max:255'],
            'contacto_correo' => ['nullable', 'email', 'max:255'],
            'contacto_telefono' => ['nullable', 'string', 'max:20'],
            'contacto_direccion' => ['nullable', 'string', 'max:255'],
        ]);

        $grupo->update($datos);

        return redirect()->route('grupos.show', $grupo)->with('status', 'contacto-actualizado');
    }

    private function sociosDisponibles()
    {
        return Socio::where('estado', 'activo')->whereNotNull('user_id')
            ->orderBy('nombre_completo')
            ->get(['id', 'nombre_completo', 'rut', 'email', 'telefono', 'direccion']);
    }

    private function validarGrupo(Request $request, ?Grupo $grupo = null): array
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('grupos', 'nombre')->ignore($grupo?->id)],
            'socio_id' => ['nullable', Rule::exists('socios', 'id')->where(
                fn ($query) => $query->where('estado', 'activo')->whereNotNull('user_id')
            )],
            'usar_contacto_representante' => ['sometimes', 'boolean'],
            'contacto_nombre' => ['nullable', 'string', 'max:255'],
            'contacto_correo' => ['nullable', 'email', 'max:255'],
            'contacto_telefono' => ['nullable', 'string', 'max:20'],
            'contacto_direccion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.unique' => 'Ya existe un grupo con este nombre.',
            'socio_id.exists' => 'El representante debe ser un socio activo con cuenta de usuario.',
        ]);

        $socioId = array_key_exists('socio_id', $datos) ? $datos['socio_id'] : $grupo?->representante_id;
        $datos['representante_id'] = $socioId;

        if ($request->boolean('usar_contacto_representante', $grupo === null || $request->has('socio_id'))) {
            $socio = $socioId ? Socio::findOrFail($socioId) : null;
            $datos['contacto_nombre'] = $socio?->nombre_completo;
            $datos['contacto_correo'] = $socio?->email;
            $datos['contacto_telefono'] = $socio?->telefono;
            $datos['contacto_direccion'] = $socio?->direccion;
        }

        unset($datos['socio_id'], $datos['usar_contacto_representante']);

        return $datos;
    }
}
