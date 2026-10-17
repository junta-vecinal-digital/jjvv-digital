<?php

namespace App\Http\Controllers;

use App\Models\Acta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ActaController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();
        abort_unless($usuario->esDirigente() || $usuario->esSocioActivo(), 403);

        $tipos = $usuario->esDirigente() ? Acta::TIPOS : ['asamblea' => Acta::TIPOS['asamblea']];
        $filtros = $request->validate(['tipo' => ['nullable', Rule::in(array_keys($tipos))]]);

        return view('actas.index', [
            'actas' => Acta::whereIn('tipo', array_keys($tipos))
                ->when($filtros['tipo'] ?? null, fn ($q, $tipo) => $q->where('tipo', $tipo))
                ->orderByDesc('fecha')->orderByDesc('id')->paginate(20)->withQueryString(),
            'tipos' => $tipos,
        ]);
    }

    public function create()
    {
        return view('actas.create');
    }

    public function store(Request $request)
    {
        $datos = $request->validate([
            'fecha' => ['required', 'date_format:Y-m-d'],
            'tipo' => ['required', Rule::in(array_keys(Acta::TIPOS))],
            'acuerdos' => ['required', 'string'],
            'escaneo' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        if ($request->hasFile('escaneo')) {
            $datos['escaneo_path'] = $request->file('escaneo')->store('actas', 'local');
        }
        unset($datos['escaneo']);
        $datos['registrado_por'] = $request->user()->id;

        $acta = Acta::create($datos);

        return redirect()->route('actas.show', $acta)->with('status', 'acta-registrada');
    }

    public function show(Request $request, Acta $acta)
    {
        abort_unless($acta->puedeConsultar($request->user()), 403);

        return view('actas.show', ['acta' => $acta->load('registrador')]);
    }

    public function escaneo(Request $request, Acta $acta)
    {
        abort_unless($acta->puedeConsultar($request->user()), 403);
        abort_unless($acta->escaneo_path && Storage::disk('local')->exists($acta->escaneo_path), 404);

        return Storage::disk('local')->response(
            $acta->escaneo_path,
            'acta-'.$acta->id.'.'.pathinfo($acta->escaneo_path, PATHINFO_EXTENSION),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
        );
    }
}
