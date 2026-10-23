<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\Ingreso;
use App\Models\Socio;
use App\Rules\RutChileno;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/** Dirigentes: revisan solicitudes, emiten en ventanilla, anulan certificados. */
class CertificadoAdminController extends Controller
{
    public function index(Request $request)
    {
        $certificados = Certificado::with(['socio', 'descargas.usuario'])
            ->when($request->estado, fn ($q, $v) => $q->where('estado', $v))
            ->when($request->tipo, fn ($q, $v) => $q->where('tipo', $v))
            ->when($request->buscar, function ($q, $texto) {
                $q->where(function ($q) use ($texto) {
                    $q->where('nombre_solicitante', 'like', "%{$texto}%")
                        ->orWhere('rut_solicitante', 'like', "%{$texto}%")
                        ->orWhere('folio', 'like', "%{$texto}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('certificados.admin.index', compact('certificados'));
    }

    public function create(Request $request)
    {
        return view('certificados.admin.create', [
            'sociosActivos' => Socio::where('estado', 'activo')->orderBy('nombre_completo')->get(),
            'socioSel' => $request->socio ? Socio::where('estado', 'activo')->find($request->socio) : null,
            'destinatarios' => Certificado::query()->distinct()->orderBy('dirigido_a')->limit(50)->pluck('dirigido_a'),
        ]);
    }

    /** Emisión directa en ventanilla: valida el pago; el ingreso se registra en Finanzas (RFS-27). */
    public function store(Request $request)
    {
        $request->merge(['rut_solicitante' => RutChileno::normalizar($request->rut_solicitante)]);
        $request->merge(['aporte' => $request->input('aporte', \App\Models\Parametro::entero('certificado_residencia_valor'))]);

        $datos = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(Certificado::TIPOS))],
            'socio_id' => ['nullable', 'exists:socios,id'],
            ...$this->reglasComunes($request->tipo),
            ...$this->reglasPagoVentanilla($request),
        ]);

        $socio = isset($datos['socio_id']) ? Socio::find($datos['socio_id']) : null;

        if ($datos['tipo'] === 'socio') {
            if (! $socio || $socio->estado !== 'activo') {
                return back()->withInput()->withErrors([
                    'socio_id' => 'Para un certificado de socio debes elegir un socio activo.',
                ]);
            }
        }

        // Campos de control de la validación: no se guardan en el certificado.
        unset($datos['residencia_6_meses'], $datos['pago_verificado']);

        $certificado = Certificado::create([
            ...$datos,
            'user_id' => $datos['tipo'] === 'socio' ? null : $socio?->user_id,
            'solo_impresion' => $datos['tipo'] === 'socio',
            'documentos_verificados' => $request->boolean('documentos_verificados'),
        ]);
        $certificado->emitir($request->user());

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('admin.certificados.index')->withInput(['operacion' => 'certificado_'.$certificado->id]))->with('status', 'certificado-emitido');
    }

    public function show(Certificado $certificado)
    {
        $certificado->load(['socio', 'solicitante', 'otorgante', 'descargas.usuario']);

        return view('certificados.admin.show', ['certificado' => $certificado]);
    }

    /** Valida la solicitud en línea (pago y documentos) y la emite (RFS-21). */
    public function emitir(Request $request, Certificado $certificado)
    {
        abort_unless($certificado->estado === 'solicitado', 422, 'Solo se pueden emitir solicitudes pendientes.');

        $request->merge(['rut_solicitante' => RutChileno::normalizar($request->rut_solicitante)]);

        if ($request->boolean('validar_emitir')) {
            $request->merge([
                'nombre_solicitante' => $certificado->nombre_solicitante, 'rut_solicitante' => $certificado->rut_solicitante,
                'direccion' => $certificado->direccion, 'dirigido_a' => $certificado->dirigido_a,
                'documentos_verificados' => 1, 'pago_verificado' => 1,
            ]);
        }
        $datos = $request->validate($this->reglasComunes($certificado->tipo));

        if ($certificado->tipo === 'residencia' && ($certificado->aporte ?? \App\Models\Parametro::entero('certificado_residencia_valor')) > 0) {
            $request->validate(['pago_verificado' => ['accepted']]);
        }

        $certificado->fill([
            ...$datos,
            'documentos_verificados' => $request->boolean('documentos_verificados'),
        ])->save();

        if ($certificado->tipo === 'socio') {
            $certificado->forceFill(['solo_impresion' => true, 'user_id' => null])->save();
        }

        $certificado->emitir($request->user());

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('admin.certificados.show', $certificado))->with('status', 'certificado-emitido');
    }

    public function rechazar(Request $request, Certificado $certificado)
    {
        abort_unless($certificado->estado === 'solicitado', 422, 'Solo se pueden rechazar solicitudes pendientes.');

        $datos = $request->validate(['observaciones' => ['required', 'string', 'max:500']]);

        $certificado->forceFill(['estado' => 'rechazado', 'observaciones' => $datos['observaciones']])->save();

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('admin.certificados.show', $certificado))->with('status', 'certificado-rechazado');
    }

    public function anular(Request $request, Certificado $certificado)
    {
        abort_unless($certificado->estado === 'emitido', 422, 'Solo se pueden anular certificados emitidos.');

        $datos = $request->validate(['observaciones' => ['required', 'string', 'max:500']]);

        $certificado->forceFill(['estado' => 'anulado', 'observaciones' => $datos['observaciones']])->save();

        return ($request->filled('operacion') ? back()->withInput($request->only('operacion')) : redirect()->route('admin.certificados.show', $certificado))->with('status', 'certificado-anulado');
    }

    /** PDF para revisión (borrador con marca de agua) o definitivo si ya fue emitido (RFS-36). */
    public function pdf(Certificado $certificado)
    {
        return $certificado->toPdf()->stream($certificado->nombreArchivoPdf());
    }

    /** Solo presidente y secretario pueden abrir el comprobante de pago. */
    public function comprobante(Certificado $certificado)
    {
        abort_unless(
            $certificado->comprobante_path && Storage::disk('local')->exists($certificado->comprobante_path),
            404
        );

        return Storage::disk('local')->response($certificado->comprobante_path);
    }

    private function reglasComunes(?string $tipo): array
    {
        return [
            'nombre_solicitante' => ['required', 'string', 'max:255'],
            'rut_solicitante' => ['required', 'string', 'max:12', new RutChileno],
            'direccion' => ['required', 'string', 'max:255'],
            'dirigido_a' => ['required', 'string', 'max:255'],
            // RO-02: para residencia se exige ver físicamente carnet y boleta.
            'documentos_verificados' => $tipo === 'residencia' ? ['accepted'] : ['nullable'],
        ];
    }

    /** Solo en ventanilla: el pago se registra en el momento y se confirman los 6 meses (RFS-27). */
    private function reglasPagoVentanilla(Request $request): array
    {
        if ($request->tipo !== 'residencia') {
            return [];
        }
        $conAporte = $request->integer('aporte') > 0;

        return [
            'residencia_6_meses' => ['accepted'],
            'aporte' => ['required', 'integer', 'min:0', 'max:100000000'],
            'pago_verificado' => $conAporte ? ['accepted'] : ['exclude'],
            'medio_pago' => $conAporte ? ['required', Rule::in(array_keys(Ingreso::MEDIOS))] : ['exclude'],
            'talonario' => $conAporte ? ['nullable', 'required_if:medio_pago,efectivo', 'string', 'max:30'] : ['exclude'],
            'numero_transaccion' => $conAporte ? ['required', 'string', 'max:50'] : ['exclude'],
        ];
    }
}
