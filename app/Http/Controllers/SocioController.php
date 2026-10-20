<?php

namespace App\Http\Controllers;

use App\Models\Socio;
use App\Models\User;
use App\Rules\RutChileno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        $request->merge(['rut' => RutChileno::normalizar($request->rut)]);

        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', new RutChileno, 'unique:socios,rut'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'residente_desde' => $this->reglaResidencia(),
            'carnet' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'boleta' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], $this->mensajes());

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

    /** El vecino corrige su solicitud mientras está pendiente (incluye las reabiertas tras un rechazo). */
    public function reenviar(Request $request)
    {
        $socio = $request->user()->socio;

        abort_unless($socio && $socio->estado === 'pendiente', 403, 'Tu solicitud ya no se puede modificar.');

        $request->merge(['rut' => RutChileno::normalizar($request->rut)]);

        $datos = $request->validate([
            'nombre_completo' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', new RutChileno, Rule::unique('socios', 'rut')->ignore($socio->id)],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
            'residente_desde' => $this->reglaResidencia(),
            'carnet' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'boleta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], $this->mensajes());

        $cambios = collect($datos)->except(['carnet', 'boleta'])->all();
        $cambios += $this->reemplazarDocumentos($request, $socio);

        $socio->update($cambios);

        return redirect()->route('socios.create')->with('status', 'solicitud-corregida');
    }

    // ---------- Dirigentes: revisar solicitudes ----------

    public function index(Request $request)
    {
        $archivo = $request->routeIs('socios.archivo') || $request->query('seccion') === 'archivo';
        $estado = $request->query('estado', $archivo ? 'rechazado' : 'activo');
        $estados = $archivo ? ['rechazado', 'inactivo'] : ['activo', 'pendientes', 'pendiente', 'por_firmar'];
        abort_unless(in_array($estado, $estados, true), 422);
        $socios = Socio::with(['user', 'revisor', 'cita'])
            ->whereIn('estado', in_array($estado, ['pendientes', 'pendiente', 'por_firmar']) ? ['pendiente', 'por_firmar'] : [$estado])
            ->when($request->buscar, function ($q, $texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('nombre_completo', 'like', "%{$texto}%")
                        ->orWhere('rut', 'like', "%{$texto}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('socios.index', compact('socios', 'archivo', 'estado'));
    }

    public function show(Socio $socio)
    {
        return view('socios.show', compact('socio'));
    }

    /** Crea la cuenta de un socio registrado en ventanilla (RFU-16, RFS-15). */
    public function crearCuenta(Request $request, Socio $socio)
    {
        abort_unless(
            $socio->user_id === null && $socio->estado !== 'rechazado',
            422,
            'Este socio ya tiene cuenta o no puede tenerla.'
        );

        $datos = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        // Una sola creación: la cuenta nace con rol vecino y correo verificado,
        // porque la identidad se comprobó en persona en ventanilla.
        $usuario = new User();
        $usuario->forceFill([
            'name' => $socio->nombre_completo,
            'email' => $datos['email'],
            'password' => $datos['password'],
            'role' => 'vecino',
            'email_verified_at' => now(),
        ])->save();

        $socio->forceFill(['user_id' => $usuario->id])->save();

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('socios.show', $socio))->with('status', 'cuenta-creada');
    }



    /** Cambia el estado de la ficha respetando el ciclo de vida (Socio::TRANSICIONES). */
    public function update(Request $request, Socio $socio)
    {
        abort_if($socio->user_id === $request->user()->id, 403, 'Otro presidente o secretario debe resolver tu ficha.');

        $destino = $request->validate([
            'estado' => ['required', Rule::in(array_keys(Socio::ETIQUETAS))],
        ])['estado'];

        abort_if($destino === 'inactivo' && $socio->user?->esDirigente(), 403, 'Solo el administrador puede dar de baja la ficha de un dirigente.');

        if (! $socio->puedePasarA($destino)) {
            return back()->withInput()->withErrors([
                'estado' => 'No se puede pasar la ficha de «'.Socio::ETIQUETAS[$socio->estado].'» a «'.Socio::ETIQUETAS[$destino].'».',
            ]);
        }

        $datos = $request->validate(match ($destino) {
            'rechazado' => [
                'observaciones' => ['required', 'string', 'max:500'],
            ],
            'inactivo' => [
                'motivo_baja' => ['required', 'string', 'max:500'],
                'fecha_baja' => ['required', 'date', 'before_or_equal:today'],
            ],
            'por_firmar', 'activo' => [
                'observaciones' => ['nullable', 'string', 'max:500'],
            ],
            default => [],
        });

        $cambios = ['estado' => $destino] + $datos;

        if ($destino === 'activo') {
            if ($socio->estado === 'inactivo') {
                // Reactivación: conserva la fecha de ingreso original y limpia los datos de la baja.
                $cambios += ['motivo_baja' => null, 'fecha_baja' => null];
            } else {
                // Tras la firma del libro.
                $cambios['fecha_ingreso'] = $socio->fecha_ingreso ?? now()->toDateString();
            }
        }

        $socio->update($cambios + ['revisado_por' => $request->user()->id]);

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('socios.show', $socio))->with('status', 'socio-'.$destino);
    }

    public function documentoPropio(Request $request, string $tipo)
    {
        abort_unless($request->user()->socio, 404);
        return $this->documento($request->user()->socio, $tipo);
    }

    public function documento(Socio $socio, string $tipo)
    {
        abort_unless(in_array($tipo, ['carnet', 'boleta']), 404);

        $ruta = $tipo === 'carnet' ? $socio->carnet_path : $socio->boleta_path;
        abort_unless($ruta && Storage::exists($ruta), 404);

        return Storage::response($ruta);
    }

    // ---------- Dirigentes: registrar y editar socios (RFU-06) ----------

    /** Inscripción en ventanilla: la persona está presente y los documentos se ven en persona. */
    public function crear()
    {
        return view('socios.crear');
    }

    public function guardar(Request $request)
    {
        $request->merge(['rut' => RutChileno::normalizar($request->rut)]);

        $datos = $request->validate([
            ...$this->reglasDatos(),
            'residente_desde' => $this->reglaResidencia(),
            'estado' => ['required', Rule::in(['por_firmar', 'activo'])],
            'fecha_ingreso' => ['nullable', 'required_if:estado,activo', 'date', 'before_or_equal:today'],
            'carnet' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'boleta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], $this->mensajes());

        $socio = Socio::create([
            'nombre_completo' => $datos['nombre_completo'],
            'rut' => $datos['rut'],
            'email' => $datos['email'] ?? null,
            'direccion' => $datos['direccion'],
            'telefono' => $datos['telefono'],
            'residente_desde' => $datos['residente_desde'],
            'estado' => $datos['estado'],
            'fecha_ingreso' => $datos['estado'] === 'activo' ? $datos['fecha_ingreso'] : null,
            'carnet_path' => $request->file('carnet')?->store('socios/carnets'),
            'boleta_path' => $request->file('boleta')?->store('socios/boletas'),
            'revisado_por' => $request->user()->id,
        ]);

        return redirect()->route('socios.index', ['estado' => $socio->estado === 'activo' ? 'activo' : 'pendientes'])->withInput(['operacion' => 'socio_'.$socio->id])->with('status', 'socio-creado');
    }

    public function editar(Socio $socio)
    {
        return view('socios.editar', compact('socio'));
    }

    public function actualizar(Request $request, Socio $socio)
    {
        $request->merge(['rut' => RutChileno::normalizar($request->rut)]);

        // La regla de 6 meses solo se exige si el dirigente cambia la fecha de residencia.
        $residencia = $request->residente_desde === $socio->residente_desde->toDateString()
            ? ['required', 'date', 'before_or_equal:today']
            : $this->reglaResidencia();

        $datos = $request->validate([
            ...$this->reglasDatos($socio),
            'residente_desde' => $residencia,
            'carnet' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'boleta' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], $this->mensajes());

        $cambios = collect($datos)->except(['carnet', 'boleta'])->all();
        $cambios += $this->reemplazarDocumentos($request, $socio);

        $socio->update($cambios);

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('socios.show', $socio))->with('status', 'socio-editado');
    }

    // ---------- Apoyo ----------

    /** Guarda los archivos nuevos (si se subieron), borra los anteriores y devuelve las rutas a actualizar. */
    private function reemplazarDocumentos(Request $request, Socio $socio): array
    {
        $rutas = [];

        foreach (['carnet' => 'socios/carnets', 'boleta' => 'socios/boletas'] as $campo => $carpeta) {
            if ($request->hasFile($campo)) {
                $anterior = $socio->{$campo.'_path'};
                $rutas[$campo.'_path'] = $request->file($campo)->store($carpeta);

                if ($anterior) {
                    Storage::delete($anterior);
                }
            }
        }

        return $rutas;
    }

    private function reglasDatos(?Socio $socio = null): array
    {
        return [
            'nombre_completo' => ['required', 'string', 'max:255'],
            'rut' => ['required', 'string', 'max:12', new RutChileno, Rule::unique('socios', 'rut')->ignore($socio?->id)],
            'email' => ['nullable', 'email', 'max:255'],
            'direccion' => ['required', 'string', 'max:255'],
            'telefono' => ['required', 'string', 'max:20'],
        ];
    }

    private function reglaResidencia(): array
    {
        return ['required', 'date', 'before_or_equal:'.now()->subMonths(6)->toDateString()];
    }

    private function mensajes(): array
    {
        return [
            'residente_desde.before_or_equal' => 'Debe residir en el sector hace al menos 6 meses.',
            'rut.unique' => 'Este RUT ya figura como socio de la junta. Acércate a la sede para regularizar tu situación.',
        ];
    }
}
