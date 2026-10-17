<?php

namespace App\Http\Controllers;

use App\Models\Parametro;
use App\Models\TipoActividad;
use App\Models\TipoInsumo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Administrador: parámetros y catálogo de actividades gratuitas (RFS-44/88). */
class ParametroController extends Controller
{
    public function index()
    {
        return view('parametros.index', [
            'parametros' => Parametro::whereNotIn('clave', ['arriendo_valor', 'arriendo_garantia'])->orderBy('id')->get(),
            'tiposActividad' => TipoActividad::orderBy('id')->get(),
            'tiposInsumo' => TipoInsumo::orderBy('categoria')->orderBy('nombre')->get()->groupBy('categoria'),
        ]);
    }

    public function update(Request $request)
    {
        $reglas = [
            'sede_hora_inicio' => ['required', 'date_format:H:i'],
            'sede_hora_fin' => ['required', 'date_format:H:i'],
            'sede_duracion_bloque_min' => ['required', 'integer', 'in:30,60'],
            'certificado_vigencia_dias' => ['required', 'integer', 'min:1', 'max:365'],
            'certificado_plazo_descarga_dias' => ['required', 'integer', 'min:1', 'max:30'],
            'certificado_residencia_valor' => ['required', 'integer', 'min:0'],
            'arriendo_plazo_confirmacion_horas' => ['required', 'integer', 'min:1', 'max:336'],
        ];

        $datos = $request->validate(
            collect($reglas)->mapWithKeys(fn ($regla, $clave) => ["valores.$clave" => $regla])->all()
        );

        $valores = $datos['valores'];

        if ($valores['sede_hora_fin'] <= $valores['sede_hora_inicio']) {
            return back()->withInput()->withErrors([
                'valores.sede_hora_fin' => 'La hora de cierre debe ser posterior a la de apertura.',
            ]);
        }

        foreach ($valores as $clave => $valor) {
            $parametro = Parametro::where('clave', $clave)->firstOrFail();

            // Solo se guarda si cambió: así cada cambio queda en la auditoría.
            if ((string) $parametro->valor !== (string) $valor) {
                $parametro->forceFill([
                    'valor' => (string) $valor,
                    'actualizado_por' => $request->user()->id,
                ])->save();
            }
        }

        return redirect()->route('parametros.index')->with('status', 'parametros-guardados');
    }

    public function crearActividad(Request $request)
    {
        $datos = $request->validate(['nombre_actividad' => ['required', 'string', 'max:120', Rule::unique('tipos_actividad', 'nombre')]]);
        TipoActividad::create(['clave' => 'act_'.Str::lower(Str::random(12)), 'nombre' => $datos['nombre_actividad'], 'activo' => true]);

        return redirect()->route('parametros.index')->with('status', 'actividad-guardada');
    }

    public function crearInsumo(Request $request)
    {
        $datos = $request->validate([
            'nombre_insumo' => ['required', 'string', 'max:255', Rule::unique('tipos_insumo', 'nombre')],
            'categoria_insumo' => ['required', Rule::in(array_keys(TipoInsumo::CATEGORIAS))],
        ]);
        TipoInsumo::create(['nombre' => $datos['nombre_insumo'], 'categoria' => $datos['categoria_insumo']]);

        return redirect()->route('parametros.index')->with('status', 'insumo-agregado');
    }

    public function actualizarActividad(Request $request, TipoActividad $tipoActividad)
    {
        return DB::transaction(function () use ($request, $tipoActividad) {
            // Serializa cambios de catálogo con las nuevas reservas manuales.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->update(['valor' => DB::raw('valor')]);
            $datos = $request->validate([
                'nombre_actividad' => ['required', 'string', 'max:120', Rule::unique('tipos_actividad', 'nombre')->ignore($tipoActividad->id)],
                'activo' => ['required', 'boolean'],
            ]);
            $tipoActividad->update(['nombre' => $datos['nombre_actividad'], 'activo' => $datos['activo']]);

            return redirect()->route('parametros.index')->with('status', 'actividad-guardada');
        });
    }
}
