<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Rules\RutChileno;
use App\Models\DescargaCertificado;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/** Vecino: solicita certificados y descarga los que ya fueron emitidos. */
class CertificadoController extends Controller
{
    public function index(Request $request)
    {
        $usuario = $request->user();

        return view('certificados.index', [
            'socio' => $usuario->socio,
            'certificados' => Certificado::delUsuario($usuario)->latest()->get(),
            'destinatarios' => Certificado::delUsuario($usuario)
                ->distinct()->orderBy('dirigido_a')->limit(50)->pluck('dirigido_a'),
        ]);
    }

    public function store(Request $request)
    {
        $usuario = $request->user();
        $socio = $usuario->socio;

        $tipo = $request->validate([
            'tipo' => ['required', Rule::in(array_keys(Certificado::TIPOS))],
        ])['tipo'];

        $pendientes = Certificado::delUsuario($usuario)->where('estado', 'solicitado')->count();
        if ($tipo === 'residencia' && $pendientes >= 3) {
            return back()->withInput()->withErrors([
                'tipo' => 'Ya tienes 3 solicitudes pendientes. Espera a que la directiva las revise.',
            ]);
        }

        if ($tipo === 'socio') {
            if (! $socio || $socio->estado !== 'activo') {
                return back()->withInput()->withErrors([
                    'tipo' => 'El certificado de socio solo está disponible para socios activos.',
                ]);
            }

            $datos = $request->validateWithBag('certificadoSocio', ['dirigido_a' => ['required', 'string', 'max:255']]);
            $datos += [
                'nombre_solicitante' => $socio->nombre_completo,
                'rut_solicitante' => $socio->rut,
                'direccion' => $socio->direccion,
                'socio_id' => $socio->id,
            ];
            $presidentes = User::where('role', 'presidente')->get();
            if ($presidentes->count() !== 1) {
                return back()->withInput()->withErrors(['otorgante' => 'Administración debe asignar un único presidente para emitir tu certificado de socio.'], 'certificadoSocio');
            }

            DB::transaction(function () use ($datos, $usuario, $presidentes) {
                DB::table('parametros')->where('clave', 'certificado_vigencia_dias')->update(['valor' => DB::raw('valor')]);
                $certificado = Certificado::create($datos + ['tipo' => 'socio', 'user_id' => $usuario->id, 'solo_impresion' => false]);
                $certificado->emitir($presidentes->first());
            });

            return redirect()->route('certificados.index')->with('status', 'certificado-socio-emitido');
        } else {
            // El aporte es voluntario; solo un aporte positivo necesita respaldo.
            $request->merge(['rut_solicitante' => RutChileno::normalizar($request->rut_solicitante)]);
            $request->merge(['aporte' => $request->input('aporte', \App\Models\Parametro::entero('certificado_residencia_valor'))]);
            $conAporte = $request->integer('aporte') > 0;

            $datos = $request->validate([
                'nombre_solicitante' => ['required', 'string', 'max:255'],
                'rut_solicitante' => ['required', 'string', 'max:12', new RutChileno],
                'direccion' => ['required', 'string', 'max:255'],
                'dirigido_a' => ['required', 'string', 'max:255'],
                'aporte' => ['required', 'integer', 'min:0', 'max:100000000'],
                'numero_transaccion' => $conAporte ? ['required', 'string', 'max:50'] : ['exclude'],
                'comprobante' => $conAporte ? ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'] : ['exclude'],
            ]);

            $datos['comprobante_path'] = $conAporte ? $request->file('comprobante')->store('comprobantes', 'local') : null;
            $datos['medio_pago'] = $conAporte ? 'transferencia' : null;
            unset($datos['comprobante']);
            $datos['socio_id'] = $socio?->id;
        }

        Certificado::create($datos + ['tipo' => $tipo, 'user_id' => $usuario->id]);

        return redirect()->route('certificados.index')->with('status', 'certificado-solicitado');
    }

    public function descargar(Request $request, Certificado $certificado)
    {
        abort_unless(
            Certificado::delUsuario($request->user())->whereKey($certificado->id)->exists()
                && $certificado->estado === 'emitido',
            404
        );

        if ($certificado->tipo === 'residencia') {
            abort_unless(
                $certificado->puedeDescargarse(),
                410,
                'El plazo para descargar este certificado venció. Puedes retirarlo en la sede.'
            );

            // Solo se registran las descargas del certificado de residencia (decisión del proyecto).
            DescargaCertificado::create([
                'certificado_id' => $certificado->id,
                'user_id' => $request->user()->id,
                'ip' => $request->ip(),
                'descargado_en' => now(),
            ]);
            
            $certificado->registrarAuditoria('descargado', [
                'descarga' => ['antes' => null, 'despues' => ['ip' => $request->ip()]],
            ]);
        }

        return $certificado->toPdf()->stream($certificado->nombreArchivoPdf());
    }
}
