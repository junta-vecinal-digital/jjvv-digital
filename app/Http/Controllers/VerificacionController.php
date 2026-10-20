<?php

namespace App\Http\Controllers;

use App\Models\Certificado;
use App\Models\HorarioAtencion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Verificación pública de certificados (sin iniciar sesión). */
class VerificacionController extends Controller
{
    public function formulario()
    {
        return view('verificar', ['horariosAtencion' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get()->groupBy('dia_semana'), 'consultado' => false, 'certificado' => null, 'estado' => null, 'rutEnmascarado' => null]);
    }

    public function buscar(Request $request)
    {
        $datos = $request->validate(['codigo' => ['required', 'string', 'max:30']]);

        return redirect()->route('verificar.mostrar', $this->limpiar($datos['codigo']));
    }

    public function mostrar(string $codigo)
    {
        $certificado = Certificado::with('otorgante')
            ->where('codigo_verificacion', $this->limpiar($codigo))
            ->first();

        $estado = $certificado?->estadoPublico();

        // Solicitudes pendientes o rechazadas no existen públicamente.
        if (! $estado) {
            $certificado = null;
        }

        return view('verificar', ['horariosAtencion' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get()->groupBy('dia_semana'),
            'consultado' => true,
            'certificado' => $certificado,
            'estado' => $estado,
            'rutEnmascarado' => $certificado
                ? Str::mask($certificado->rut_solicitante, '*', 0, max(strlen($certificado->rut_solicitante) - 5, 0))
                : null,
        ]);
    }

    private function limpiar(string $codigo): string
    {
        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $codigo));
    }
}
