<?php

namespace App\Http\Controllers;

use App\Models\Proyecto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProyectoController extends Controller
{
    public function index(Request $request)
    {
        $filtros = $request->validate(['estado' => ['nullable', Rule::in(array_keys(Proyecto::ESTADOS))]]);

        return view('proyectos.index', [
            'proyectos' => Proyecto::with('registrador')->when($filtros['estado'] ?? null, fn ($q, $estado) => $q->where('estado', $estado))
                ->orderByDesc('fecha_postulacion')->orderByDesc('id')->paginate(20)->withQueryString(),
            'organismos' => Proyecto::distinct()->orderBy('organismo')->pluck('organismo'),
            'postuladores' => User::whereIn('role', ['presidente', 'secretario', 'tesorero'])->orderBy('name')->get(['id', 'name', 'role']),
        ]);
    }

    public function create()
    {
        return view('proyectos.form', $this->datosFormulario());
    }

    public function store(Request $request)
    {
        $proyecto = Proyecto::create($this->validar($request) + ['registrado_por' => $request->user()->id]);

        return redirect()->route('proyectos.index')->withInput(['operacion' => 'proyecto_'.$proyecto->id])->with('status', 'Proyecto registrado.');
    }

    public function show(Proyecto $proyecto)
    {
        return view('proyectos.show', $this->datosFormulario($proyecto->load('registrador')));
    }

    public function edit(Proyecto $proyecto)
    {
        return view('proyectos.form', $this->datosFormulario($proyecto));
    }

    public function update(Request $request, Proyecto $proyecto)
    {
        $proyecto->update($this->validar($request, $proyecto));

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('proyectos.show', $proyecto))->with('status', 'Proyecto actualizado.');
    }

    private function validar(Request $request, ?Proyecto $proyecto = null): array
    {
        $conservar = $proyecto !== null && $request->input('postulador_id') === '__conservar';
        $datos = $request->validate([
            'titulo' => ['required', 'string', 'max:255'],
            'organismo' => ['required', 'string', 'max:255'],
            'fecha_postulacion' => ['required', 'date_format:Y-m-d'],
            'estado' => ['required', Rule::in(array_keys(Proyecto::ESTADOS))],
            'monto' => ['nullable', 'integer', 'min:0'],
            'postulador_id' => $conservar ? ['required', 'in:__conservar']
                : ['required', 'integer', Rule::exists('users', 'id')->whereIn('role', ['presidente', 'secretario', 'tesorero'])],
        ]);

        if ($conservar) {
            unset($datos['postulador_id']);
        } else {
            $datos['postulado_por'] = User::whereIn('role', ['presidente', 'secretario', 'tesorero'])
                ->findOrFail($datos['postulador_id'])->name;
        }

        return $datos;
    }

    private function datosFormulario(?Proyecto $proyecto = null): array
    {
        return [
            'proyecto' => $proyecto,
            'organismos' => Proyecto::distinct()->orderBy('organismo')->pluck('organismo'),
            'postuladores' => User::whereIn('role', ['presidente', 'secretario', 'tesorero'])->orderBy('name')->get(['id', 'name', 'role']),
        ];
    }
}
