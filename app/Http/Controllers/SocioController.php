<?php

namespace App\Http\Controllers;

use App\Models\Socio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SocioController extends Controller
{
    // ---------- Vecino: solicitar inscripción ----------

    public function create(Request $request)
    {
        return view('socios.create', ['socio' => $request->user()->socio]);
    }

    public function store(Request $request)
    {
        abort_if($request->user()->socio, 403, 'Ya tienes una solicitud registrada.');

        // Normaliza el RUT para que "12.345.678-9" y "12345678-9" cuenten como el mismo
        $request->merge(['rut' => strtoupper(str_replace(['.', ' '], '', (string) $request->rut))]);

        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', 'unique:socios,rut'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'residente_desde' => ['required', 'date', 'before_or_equal:' . now()->subMonths(6)->toDateString()],
            'carnet' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'boleta' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [
            'residente_desde.before_or_equal' => 'Debes residir en el sector hace al menos 6 meses.',
        ]);

        $request->user()->socio()->create([
            'nombre_completo' => $datos['nombre_completo'],
            'rut' => $datos['rut'],
            'email' => $request->user()->email,
            'direccion' => $datos['direccion'],
            'telefono' => $datos['telefono'],
            'residente_desde' => $datos['residente_desde'],
            'carnet_path' => $request->file('carnet')->store('socios/carnets'),
            'boleta_path' => $request->file('boleta')->store('socios/boletas'),
        ]);

        return redirect()->route('socios.create')->with('status', 'solicitud-enviada');
    }

    // ---------- Dirigentes: revisar solicitudes ----------

    public function index(Request $request)
    {
        $socios = Socio::query()
            ->when($request->estado, fn ($q, $estado) => $q->where('estado', $estado))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('socios.index', compact('socios'));
    }

    public function show(Socio $socio)
    {
        return view('socios.show', compact('socio'));
    }

    public function update(Request $request, Socio $socio)
    {
        $datos = $request->validate([
            'estado' => ['required', 'in:por_firmar,activo,rechazado'],
            'observaciones' => ['required_if:estado,rechazado', 'nullable', 'string', 'max:500'],
        ]);

        if ($datos['estado'] === 'activo' && ! $socio->fecha_ingreso) {
            $datos['fecha_ingreso'] = now()->toDateString();
        }

        $socio->update($datos + ['revisado_por' => $request->user()->id]);

        return redirect()->route('socios.show', $socio)->with('status', 'socio-actualizado');
    }

    public function documento(Socio $socio, string $tipo)
    {
        abort_unless(in_array($tipo, ['carnet', 'boleta']), 404);

        $ruta = $tipo === 'carnet' ? $socio->carnet_path : $socio->boleta_path;
        abort_unless($ruta && Storage::exists($ruta), 404);

        return Storage::response($ruta);
    }
}