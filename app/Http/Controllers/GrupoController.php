<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use App\Models\Socio;
use App\Models\Reserva;
use App\Models\User;
use App\Rules\RutChileno;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Grupos y organizaciones de la sede (RFU-29, RFU-30, RFS-38 a RFS-40). */
class GrupoController extends Controller
{
    public function index()
    {
        return view('grupos.index', [
            'grupos' => Grupo::with('representante')->orderBy('nombre')->get(),
            'personas' => $this->personasDisponibles(),
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
            'personas' => $this->personasDisponibles(),
            'horas' => $bloques['inicios'],
            'horasFin' => $bloques['fines'],
        ]);
    }

    public function update(Request $request, Grupo $grupo)
    {
        $grupo->update($this->validarGrupo($request, $grupo));

        return redirect()->route('grupos.show', $grupo)->with('status', 'grupo-actualizado');
    }

    /** Conserva el acceso de asignación desde una ficha; la cuenta ya no es obligatoria. */
    public function asignarRepresentante(Request $request, Grupo $grupo)
    {
        $datos = $request->validate([
            'socio_id' => ['required', 'exists:socios,id'],
        ]);

        $socio = Socio::findOrFail($datos['socio_id']);
        if (! RutChileno::esValido($socio->rut)) {
            return back()->withErrors(['socio_id' => 'Completa un RUT válido en la ficha antes de asignar al representante.']);
        }

        $grupo->update([
            'representante_id' => $socio->id,
            'representante_usuario_id' => $socio->user_id,
            'representante_nombre' => $socio->nombre_completo,
            'representante_rut' => $socio->rut,
            'representante_correo' => $socio->email,
            'representante_telefono' => $socio->telefono,
            'representante_direccion' => $socio->direccion,
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

    private function personasDisponibles(): array
    {
        $personas = [];
        foreach (Socio::with('user')->whereIn('estado', ['activo', 'inactivo'])->orderBy('nombre_completo')->get() as $socio) {
            $personas['socio:'.$socio->id] = [
                'etiqueta' => $socio->nombre_completo.' · '.$socio->rut.' · Socio',
                'socio_id' => $socio->id, 'usuario_id' => $socio->user_id,
                'rut' => $socio->rut,
                'nombre' => $socio->nombre_completo, 'correo' => $socio->email ?? $socio->user?->email,
                'telefono' => $socio->telefono, 'direccion' => $socio->direccion,
            ];
        }
        foreach (User::with('socio')->whereIn('role', ['presidente', 'secretario', 'tesorero'])->orderBy('name')->get() as $usuario) {
            $personas['usuario:'.$usuario->id] = [
                'etiqueta' => $usuario->name.' · '.$usuario->nombreRol(),
                'socio_id' => $usuario->socio?->id, 'usuario_id' => $usuario->id,
                'rut' => $usuario->socio?->rut,
                'nombre' => $usuario->socio?->nombre_completo ?? $usuario->name,
                'correo' => $usuario->socio?->email ?? $usuario->email,
                'telefono' => $usuario->socio?->telefono, 'direccion' => $usuario->socio?->direccion,
            ];
        }

        return $personas;
    }

    private function validarGrupo(Request $request, ?Grupo $grupo = null): array
    {
        $personas = $this->personasDisponibles();
        $origen = $request->input('origen_representante');
        $rut = $request->input('representante_rut') ?? (is_string($origen) ? ($personas[$origen]['rut'] ?? null) : null);
        $request->merge(['representante_rut' => is_string($rut) ? RutChileno::normalizar($rut) : $rut]);
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255', Rule::unique('grupos', 'nombre')->ignore($grupo?->id)],
            'origen_representante' => ['required', 'string', Rule::in(['sin_representante', 'manual', ...array_keys($personas)])],
            'representante_nombre' => ['nullable', 'required_unless:origen_representante,sin_representante', 'string', 'max:255'],
            'representante_rut' => ['exclude_if:origen_representante,sin_representante', 'bail', 'required', 'string', 'max:12', new RutChileno],
            'representante_correo' => ['nullable', 'email', 'max:255'],
            'representante_telefono' => ['nullable', 'string', 'max:20'],
            'representante_direccion' => ['nullable', 'string', 'max:255'],
            'usar_contacto_representante' => ['sometimes', 'boolean'],
            'contacto_nombre' => ['nullable', 'string', 'max:255'],
            'contacto_correo' => ['nullable', 'email', 'max:255'],
            'contacto_telefono' => ['nullable', 'string', 'max:20'],
            'contacto_direccion' => ['nullable', 'string', 'max:255'],
        ], [
            'nombre.unique' => 'Ya existe un grupo con este nombre.',
            'representante_nombre.required_unless' => 'Ingresa el nombre del representante.',
        ]);

        $origen = $datos['origen_representante'];
        // Un nombre o RUT distinto identifica a una persona ingresada manualmente.
        if (isset($personas[$origen]) && (trim($datos['representante_nombre']) !== $personas[$origen]['nombre']
            || $datos['representante_rut'] !== $personas[$origen]['rut'])) {
            $origen = 'manual';
        }
        $datos['representante_id'] = $personas[$origen]['socio_id'] ?? null;
        $datos['representante_usuario_id'] = $personas[$origen]['usuario_id'] ?? null;
        if ($origen === 'sin_representante') {
            foreach (['nombre', 'rut', 'correo', 'telefono', 'direccion'] as $campo) {
                $datos['representante_'.$campo] = null;
            }
        }

        if ($request->boolean('usar_contacto_representante', true)) {
            foreach (['nombre', 'correo', 'telefono', 'direccion'] as $campo) {
                $datos['contacto_'.$campo] = $datos['representante_'.$campo] ?? null;
            }
        }

        unset($datos['origen_representante'], $datos['usar_contacto_representante']);

        return $datos;
    }
}
