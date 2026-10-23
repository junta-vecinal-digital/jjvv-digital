<?php

namespace Database\Seeders;

use App\Models\{Acta, Certificado, Cita, DescargaCertificado, Devolucion, Egreso, Garantia, Grupo, HorarioAtencion, HorarioFijo, Ingreso, ItemInventario, Parametro, Prestamo, Proyecto, Reserva, Socio, SolicitudInsumo, SolicitudInsumoLinea, TipoInsumo, User};
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{Auth, DB, Storage};
use RuntimeException;

/** Escenarios ficticios independientes de las cuentas y operaciones existentes. */
class DemoSeeder extends Seeder
{
    private const ADJUNTO = 'demo/documento-demostracion.pdf';

    private array $usuarios = [];
    private array $socios = [];
    private int $rutBase = 61000000;

    public function run(): void
    {
        if (! app()->environment('local')) {
            throw new RuntimeException('DemoSeeder solo puede ejecutarse en el entorno local.');
        }

        DB::transaction(function () {
            // Serializa cargas simultáneas sin cambiar el parámetro.
            DB::table('parametros')->where('clave', 'sede_hora_inicio')->lockForUpdate()->first();
            if (User::where('email', 'admin@demo.example')->exists()) {
                $this->command?->info('La demostración ya existe. Se conservan sus datos y cambios.');
                return;
            }

            Storage::disk('local')->put(self::ADJUNTO, Pdf::loadHTML(
                '<h1>DEMO - Documento ficticio</h1><p>Muestra para revisar adjuntos de JJVV Digital.</p>'
                .'<p>No acredita identidad, residencia ni pagos reales.</p>'
            )->output());

            $anterior = Auth::user();
            try {
                $this->personas();
                Auth::setUser($this->usuarios['secretario']);
                $this->certificados();
                $this->reservas();
                $this->insumos();
                Auth::setUser($this->usuarios['tesorero']);
                $this->finanzas();
                Auth::setUser($this->usuarios['presidente']);
                $this->otros();
            } finally {
                $anterior ? Auth::setUser($anterior) : Auth::forgetUser();
            }
            $this->command?->info('Demostración creada. Consulta DEMO.md para cuentas y recorridos.');
        });
    }

    private function usuario(string $clave, string $rol = 'vecino'): User
    {
        if (User::where('email', $clave.'@demo.example')->exists()) {
            throw new RuntimeException('Ya existe la cuenta reservada para DEMO: '.$clave);
        }
        $usuario = new User;
        $usuario->forceFill(['name' => 'DEMO '.$clave, 'email' => $clave.'@demo.example',
            'role' => $rol, 'password' => 'DemoJJVV2026!', 'email_verified_at' => now()])->save();
        return $this->usuarios[$clave] = $usuario;
    }

    private function rut(): string
    {
        do {
            $numero = (string) ++$this->rutBase;
            $suma = 0;
            foreach (array_reverse(str_split($numero)) as $i => $digito) {
                $suma += (int) $digito * (2 + $i % 6);
            }
            $dv = 11 - $suma % 11;
            $rut = $numero.'-'.match ($dv) { 11 => '0', 10 => 'K', default => (string) $dv };
        } while (Socio::where('rut', $rut)->exists() || Grupo::where('representante_rut', $rut)->exists());
        return $rut;
    }

    private function ficha(string $clave, string $estado, ?User $usuario): Socio
    {
        return $this->socios[$clave] = Socio::create([
            'user_id' => $usuario?->id, 'nombre_completo' => 'DEMO '.$clave,
            'rut' => $this->rut(), 'email' => $usuario?->email,
            'direccion' => 'Pasaje Demostración 123', 'telefono' => '900000000',
            'residente_desde' => today()->subYears(3), 'estado' => $estado,
            'carnet_path' => self::ADJUNTO, 'boleta_path' => self::ADJUNTO,
            'fecha_ingreso' => in_array($estado, ['activo', 'inactivo']) ? today()->subYear() : null,
            'revisado_por' => $estado !== 'pendiente' ? $this->usuarios['secretario']->id : null,
            'observaciones' => $estado === 'rechazado' ? 'DEMO: boleta ilegible; solicitar corrección.' : null,
            'motivo_baja' => $estado === 'inactivo' ? 'DEMO: cambio de domicilio.' : null,
            'fecha_baja' => $estado === 'inactivo' ? today()->subDays(20) : null,
        ]);
    }

    private function personas(): void
    {
        foreach (['admin', 'presidente', 'secretario', 'tesorero'] as $rol) {
            $this->usuario($rol, $rol);
        }
        Auth::setUser($this->usuarios['secretario']);
        foreach (['presidente', 'tesorero'] as $rol) {
            $this->ficha($rol, 'activo', $this->usuarios[$rol]);
        }
        Auth::setUser($this->usuarios['presidente']);
        $this->ficha('secretario', 'activo', $this->usuarios['secretario']);
        foreach (array_keys(Socio::ETIQUETAS) as $estado) {
            for ($i = 1; $i <= 3; $i++) {
                $clave = $estado.'-'.$i;
                $this->ficha($clave, $estado, $i < 3 ? $this->usuario($clave) : null);
            }
        }
        $this->usuario('sin-ficha');
        for ($i = 0; $i < 6; $i++) {
            $inicio = today()->addDays(2)->setTime(10, 0)->addMinutes($i * 30);
            while (Cita::where('inicio', $inicio)->exists()) $inicio->addDays(1);
            Cita::create(['inicio' => $inicio, 'creado_por' => $this->usuarios['secretario']->id,
                'socio_id' => $i < 2 ? $this->socios['por_firmar-'.($i + 1)]->id : null]);
        }
    }

    private function certificados(): void
    {
        $casos = ['pendiente', 'emitido', 'rechazado', 'anulado', 'caducado', 'descargado', 'retirado', 'sin-descarga', 'socio', 'impresion'];
        foreach ($casos as $caso) {
            for ($i = 1; $i <= 3; $i++) {
                $socio = $this->socios['activo-'.($i === 3 ? 1 : $i)];
                $tipo = in_array($caso, ['socio', 'impresion']) ? 'socio' : 'residencia';
                $dias = match ($caso) {
                    'caducado' => Parametro::entero('certificado_vigencia_dias') + 10,
                    'sin-descarga', 'retirado' => Parametro::entero('certificado_plazo_descarga_dias') + 2,
                    default => 0,
                };
                $emision = today()->subDays($dias);
                $emitido = ! in_array($caso, ['pendiente', 'rechazado']);
                $aporte = $tipo === 'residencia' && $i > 1 ? 1000 * $i : 0;
                $datos = ['tipo' => $tipo, 'estado' => match ($caso) {
                    'pendiente' => 'solicitado', 'rechazado' => 'rechazado', 'anulado' => 'anulado', default => 'emitido',
                }, 'user_id' => $caso === 'impresion' ? null : $socio->user_id, 'socio_id' => $socio->id,
                    'nombre_solicitante' => $socio->nombre_completo, 'rut_solicitante' => $socio->rut,
                    'direccion' => $socio->direccion, 'dirigido_a' => 'DEMO '.$caso.' '.$i,
                    'aporte' => $aporte, 'documentos_verificados' => $emitido,
                    'solo_impresion' => $caso === 'impresion',
                    'observaciones' => in_array($caso, ['rechazado', 'anulado']) ? 'DEMO: antecedentes incorrectos.' : null,
                    'medio_pago' => $aporte ? 'transferencia' : null,
                    'numero_transaccion' => $aporte ? 'DEMO-CERT-'.$caso.'-'.$i : null,
                    'comprobante_path' => $aporte ? self::ADJUNTO : null,
                    'created_at' => $emision->copy()->subDay()];
                if ($emitido) {
                    $correlativo = (int) Certificado::where('anio', $emision->year)->max('correlativo') + 1;
                    $datos += ['anio' => $emision->year, 'correlativo' => $correlativo,
                        'folio' => sprintf('%d-%04d', $emision->year, $correlativo),
                        'fecha_emision' => $emision, 'fecha_vencimiento' => $emision->copy()->addDays(Parametro::entero('certificado_vigencia_dias')),
                        'descarga_disponible_hasta' => $emision->copy()->endOfDay()->addDays(Parametro::entero('certificado_plazo_descarga_dias')),
                        'otorgado_por' => $this->usuarios['presidente']->id,
                        'otorgante_nombre' => $this->usuarios['presidente']->name, 'otorgante_cargo' => 'Presidente',
                        'fecha_retiro' => $caso === 'retirado' ? today() : null];
                }
                $certificado = new Certificado;
                $certificado->forceFill($datos)->save();
                if ($emitido && $aporte > 0) $certificado->registrarIngreso($this->usuarios['tesorero']);
                if ($caso === 'descargado') {
                    DescargaCertificado::create(['certificado_id' => $certificado->id, 'user_id' => $socio->user_id,
                        'ip' => '127.0.0.1', 'descargado_en' => now()]);
                    $certificado->registrarAuditoria('descargado', ['demostracion' => ['antes' => null, 'despues' => 'DEMO descarga']]);
                }
            }
        }
    }

    private function horarioRecurrente(Grupo $grupo): void
    {
        $bloques = Reserva::bloquesDeLaSede();
        foreach (range(1, 7) as $dia) {
            foreach ($bloques['inicios'] as $i => $hora) {
                $fin = $bloques['fines'][$i];
                if (HorarioFijo::errorDeConflicto($dia, $hora, $fin)) continue;
                $fijo = HorarioFijo::create(['grupo_id' => $grupo->id, 'dia_semana' => $dia,
                    'hora_inicio' => $hora, 'hora_fin' => $fin]);
                $mes = today()->addMonthNoOverflow()->startOfMonth();
                for ($fecha = $mes->copy(); $fecha->month === $mes->month; $fecha->addDay()) {
                    if ($fecha->isoWeekday() !== $dia) continue;
                    Reserva::create(['grupo_id' => $grupo->id, 'horario_fijo_id' => $fijo->id,
                        'tipo' => 'grupo', 'tipo_nombre' => 'Actividad de agrupación', 'estado' => 'confirmada',
                        'inicio' => $fecha->copy()->setTimeFromTimeString($hora),
                        'fin' => $fecha->copy()->setTimeFromTimeString($fin),
                        'motivo' => 'DEMO reunión recurrente', 'responsable_nombre' => $grupo->representante_nombre,
                        'valor_arriendo' => 0, 'valor_garantia' => 0, 'aporte_variable' => true,
                        'creado_por' => $this->usuarios['secretario']->id]);
                }
                return;
            }
        }
        throw new RuntimeException('No existe un horario semanal libre para el grupo DEMO.');
    }

    private function reservas(): void
    {
        $grupos = [];
        foreach (['Taller vecinal', 'Club comunitario', 'Sin representante'] as $i => $nombre) {
            $socio = $this->socios['activo-1'];
            $grupos[] = Grupo::create(['nombre' => 'DEMO '.$nombre,
                'representante_id' => $i === 0 ? $socio->id : null,
                'representante_usuario_id' => $i === 0 ? $socio->user_id : null,
                'representante_nombre' => $i < 2 ? ($i === 0 ? $socio->nombre_completo : 'DEMO representante externo') : null,
                'representante_rut' => $i < 2 ? ($i === 0 ? $socio->rut : $this->rut()) : null,
                'representante_telefono' => $i < 2 ? '900000000' : null,
                'representante_direccion' => $i < 2 ? 'Pasaje Demostración 123' : null]);
        }
        $casos = ['pendiente', 'confirmada', 'cancelada', 'vencida', 'pasada', 'gratuita', 'grupo', 'junta', 'reingreso'];
        foreach ($casos as $caso) {
            for ($i = 1; $i <= 3; $i++) {
                $inicio = today()->addDays($caso === 'pasada' ? -20 + $i : 2 + $i);
                $apertura = Parametro::valor('sede_hora_inicio');
                $bloque = Parametro::entero('sede_duracion_bloque_min');
                $encontrada = false;
                for ($dia = 0; $dia < 150; $dia++) {
                    $inicio->setTimeFromTimeString($apertura);
                    $fin = $inicio->copy()->addMinutes($bloque);
                    if (! Reserva::errorDeConflicto($inicio, $fin)) { $encontrada = true; break; }
                    $inicio->addDay();
                }
                if (! $encontrada) throw new RuntimeException('No se encontró un bloque libre para DEMO.');
                $gratis = in_array($caso, ['gratuita', 'grupo', 'junta']);
                $validada = ! in_array($caso, ['pendiente', 'vencida']);
                $estado = in_array($caso, ['pendiente', 'cancelada', 'vencida']) ? $caso : 'confirmada';
                $socio = $this->socios['activo-1'];
                $reserva = Reserva::create(['tipo' => $caso === 'grupo' ? 'grupo' : ($caso === 'junta' ? 'actividad' : 'arriendo'),
                    'tipo_nombre' => $caso === 'grupo' ? 'Actividad de agrupación' : ($caso === 'junta' ? 'Actividad de la junta' : 'Actividad privada'),
                    'estado' => $estado, 'inicio' => $inicio, 'fin' => $fin, 'motivo' => 'DEMO '.$caso.' '.$i,
                    'socio_id' => $i === 3 ? null : $socio->id, 'grupo_id' => $caso === 'grupo' ? $grupos[0]->id : null,
                    'creado_por' => $this->usuarios['secretario']->id, 'responsable_nombre' => $socio->nombre_completo,
                    'responsable_rut' => $socio->rut, 'responsable_direccion' => $socio->direccion,
                    'responsable_telefono' => $socio->telefono, 'canal' => $i === 3 ? 'ventanilla' : 'en_linea',
                    'valor_arriendo' => $gratis ? 0 : 5000, 'valor_garantia' => $gratis ? 0 : 10000,
                    'aporte_variable' => true, 'aporte_definido_por' => $this->usuarios['secretario']->id,
                    'aporte_definido_en' => now()->subDays(30),
                    'comprobante_arriendo_path' => $gratis ? null : self::ADJUNTO,
                    'numero_transaccion' => $gratis ? null : 'DEMO-SEDE-'.$caso.'-'.$i,
                    'vence_en' => $caso === 'vencida' ? now()->subDay() : now()->addDay(),
                    'validado_en' => $validada ? now()->subDays(29) : null,
                    'validado_por' => $validada ? $this->usuarios['secretario']->id : null,
                    'cancelada_en' => $estado === 'cancelada' ? now()->subDays(2) : null,
                    'cancelada_por' => $estado === 'cancelada' ? $this->usuarios['secretario']->id : null,
                    'created_at' => $caso === 'pendiente' ? now()->subDay() : now()->subDays(30)]);
                if (! $gratis && $validada) {
                    $registrada = $caso === 'pasada' || $caso === 'reingreso';
                    $this->garantia(['reserva_id' => $reserva->id],
                        $caso === 'cancelada' ? 'por_devolver' : ($caso === 'pasada' ? ['devuelta', 'retenida', 'retenida'][$i - 1] : 'recibida'),
                        $registrada, $i === 3, 28);
                    if ($registrada) {
                        $ingreso = $this->ingreso('DEMO sede '.$caso.' '.$i, 'aporte_reserva', 5000,
                            ['reserva_id' => $reserva->id]);
                        if ($caso === 'reingreso') $ingreso->anular('DEMO: corregir registro del aporte.', $this->usuarios['tesorero']);
                    }
                }
            }
        }
        $this->horarioRecurrente($grupos[0]);
    }

    private function insumos(): void
    {
        $items = [];
        foreach (['Sillas', 'Mesas', 'Bastones'] as $nombre) {
            $tipo = TipoInsumo::create(['nombre' => 'DEMO '.$nombre, 'categoria' => $nombre === 'Bastones' ? 'ayudas_tecnicas' : 'mobiliario']);
            $items[] = ItemInventario::create(['tipo_insumo_id' => $tipo->id, 'nombre' => $tipo->nombre,
                'cantidad' => 100, 'registrado_por' => $this->usuarios['secretario']->id]);
        }
        foreach (['pendiente', 'aprobado', 'sin-comprobante', 'por-validar', 'entregado', 'atrasado', 'devuelto', 'danado'] as $caso) {
            for ($i = 1; $i <= 3; $i++) {
                $socio = $this->socios['activo-'.($i === 3 ? 1 : $i)];
                $pendiente = $caso === 'pendiente';
                $entregado = in_array($caso, ['entregado', 'atrasado', 'devuelto', 'danado']);
                $devuelto = in_array($caso, ['devuelto', 'danado']);
                $positivo = $i > 1 || in_array($caso, ['sin-comprobante', 'por-validar']);
                $validado = ! $pendiente && $positivo && ! in_array($caso, ['sin-comprobante', 'por-validar']);
                $registrado = $validado && $devuelto;
                $estado = $pendiente ? 'pendiente' : ($devuelto ? 'devuelto' : ($entregado ? 'entregado' : 'aprobado'));
                $solicitud = SolicitudInsumo::create(['estado' => $estado,
                    'solicitante_id' => $i === 3 ? null : $socio->user_id, 'socio_id' => $i === 3 ? null : $socio->id,
                    'solicitante_nombre' => 'DEMO '.$caso.' '.$i, 'solicitante_rut' => $socio->rut,
                    'telefono_contacto' => $socio->telefono, 'direccion_contacto' => $socio->direccion,
                    'correo_contacto' => $i === 3 ? null : $socio->email, 'canal' => $i === 3 ? 'ventanilla' : 'en_linea',
                    'fecha_comprometida' => $caso === 'atrasado' || $devuelto ? today()->subDays(3) : today()->addDays(7),
                    'responsable_id' => ! $pendiente ? $this->usuarios['secretario']->id : null,
                    'responsable_nombre' => ! $pendiente ? $this->usuarios['secretario']->name : null,
                    'responsable_cargo' => ! $pendiente ? 'secretario' : null,
                    'monto' => $pendiente ? null : ($positivo ? 2000 : 0), 'garantia' => $pendiente ? null : ($positivo ? 10000 : 0),
                    'registrado_por' => $i === 3 ? $this->usuarios['secretario']->id : $socio->user_id,
                    'aprobado_por' => ! $pendiente ? $this->usuarios['secretario']->id : null,
                    'aprobado_en' => ! $pendiente ? now()->subDays(9) : null,
                    'entregado_por' => $entregado ? $this->usuarios['secretario']->id : null,
                    'fecha_entrega' => $entregado ? today()->subDays(8) : null,
                    'devuelto_por' => $devuelto ? $this->usuarios['secretario']->id : null,
                    'fecha_devolucion' => $devuelto ? today()->subDays(2) : null,
                    'pago_validado_en' => $validado ? now()->subDays(8) : null,
                    'pago_validado_por' => $validado ? $this->usuarios['secretario']->id : null,
                    'pago_registrado_en' => $registrado ? now()->subDays(7) : null,
                    'pago_registrado_por' => $registrado ? $this->usuarios['tesorero']->id : null,
                    'fecha_pago' => $positivo && ! $pendiente && $caso !== 'sin-comprobante' ? today()->subDays(8) : null,
                    'medio_pago' => $positivo ? 'transferencia' : null,
                    'numero_transaccion' => $positivo ? 'DEMO-INS-'.$caso.'-'.$i : null,
                    'comprobante_path' => $positivo && ! $pendiente && $caso !== 'sin-comprobante' ? self::ADJUNTO : null,
                    'created_at' => now()->subDays(10)]);
                foreach (array_slice($items, 0, 2) as $item) {
                    $prestamo = $entregado ? Prestamo::create(['solicitud_insumo_id' => $solicitud->id,
                        'item_inventario_id' => $item->id, 'cantidad' => 2,
                        'destinatario_nombre' => $solicitud->solicitante_nombre,
                        'responsable_nombre' => $this->usuarios['secretario']->name, 'responsable_telefono' => '900000000',
                        'fecha_solicitud' => today()->subDays(10), 'fecha_prestamo' => $solicitud->fecha_entrega,
                        'estado_salida' => 'bueno', 'fecha_devolucion' => $solicitud->fecha_devolucion,
                        'estado_regreso' => $devuelto ? ($caso === 'danado' ? 'danado' : 'bueno') : null,
                        'cantidad_danada' => $caso === 'danado' ? 1 : 0,
                        'registrado_por' => $this->usuarios['secretario']->id,
                        'devuelto_por' => $devuelto ? $this->usuarios['secretario']->id : null]) : null;
                    SolicitudInsumoLinea::create(['solicitud_insumo_id' => $solicitud->id,
                        'item_inventario_id' => $item->id, 'nombre_insumo' => $item->nombre,
                        'cantidad' => 2, 'prestamo_id' => $prestamo?->id]);
                }
                if ($registrado) {
                    $this->ingreso('DEMO insumos '.$caso.' '.$i, 'prestamo_insumos', 2000, ['solicitud_insumo_id' => $solicitud->id]);
                    $this->garantia(['solicitud_insumo_id' => $solicitud->id],
                        $caso === 'danado' ? 'retenida' : ($i === 2 ? 'por_devolver' : 'devuelta'), true, $i === 3);
                }
            }
        }
    }

    private function ingreso(string $detalle, string $concepto, int $monto, array $vinculo = []): Ingreso
    {
        return Ingreso::create($vinculo + ['concepto' => $concepto, 'monto' => $monto,
            'fecha_pago' => today()->subDays(7), 'medio_pago' => 'transferencia',
            'numero_transaccion' => 'DEMO-'.substr(md5($detalle), 0, 12),
            'comprobante_path' => self::ADJUNTO, 'observaciones' => $detalle,
            'registrado_por' => $this->usuarios['tesorero']->id,
            'created_at' => now()->subDays(7)]);
    }

    private function garantia(array $vinculo, string $estado, bool $registrada, bool $total = false, int $diasRecepcion = 8): Garantia
    {
        $resuelta = in_array($estado, ['devuelta', 'retenida']);
        $retenido = $estado === 'retenida' ? ($total ? 10000 : 3000) : 0;
        $garantia = Garantia::create($vinculo + ['monto' => 10000, 'estado' => $estado,
            'recibida_en' => now()->subDays($diasRecepcion), 'validado_por' => $this->usuarios['secretario']->id,
            'registrada_en' => $registrada ? now()->subDays(7) : null,
            'registrada_por' => $registrada ? $this->usuarios['tesorero']->id : null,
            'fecha_pago' => $registrada ? today()->subDays($diasRecepcion) : null, 'medio_pago' => $registrada ? 'transferencia' : null,
            'fecha_resolucion' => $resuelta ? today()->subDay() : null,
            'resuelta_en' => $resuelta ? now()->subDay() : null,
            'resuelta_por' => $resuelta ? $this->usuarios['tesorero']->id : null,
            'monto_devuelto' => $resuelta ? 10000 - $retenido : 0, 'monto_retenido' => $retenido,
            'motivo_retencion' => $retenido ? 'DEMO: daños constatados al finalizar el uso.' : null,
            'created_at' => now()->subDays($diasRecepcion)]);
        if ($retenido) $this->ingreso('DEMO retención por daños', 'otros', $retenido,
            ['garantia_id' => $garantia->id, 'fecha_pago' => today()->subDay(), 'created_at' => now()->subDay()]);
        return $garantia;
    }

    private function finanzas(): void
    {
        foreach (['cuota_socios', 'aporte_grupos', 'otros'] as $concepto) {
            for ($i = 1; $i <= 3; $i++) {
                $ingreso = $this->ingreso('DEMO '.$concepto.' '.$i, $concepto, 10000,
                    ['tipo_cuota' => $concepto === 'otros' ? null : ($i === 1 ? 'ordinaria' : 'extraordinaria')]);
                if ($i === 2) $ingreso->anular('DEMO: registro duplicado.', $this->usuarios['tesorero']);
                if ($i === 3 && $concepto !== 'otros') Devolucion::create(['ingreso_id' => $ingreso->id,
                    'fecha_devolucion' => today()->subDay(), 'monto' => $ingreso->monto,
                    'motivo' => 'DEMO: devolución solicitada.', 'registrado_por' => $this->usuarios['tesorero']->id]);
            }
        }
        foreach (array_keys(Egreso::CONCEPTOS) as $concepto) {
            for ($i = 1; $i <= 3; $i++) {
                $egreso = Egreso::create(['concepto' => $concepto, 'fecha_pago' => today()->subDays($i),
                    'monto' => 3000 * $i, 'numero_transaccion' => 'DEMO-EGR-'.$concepto.'-'.$i,
                    'comprobante_path' => self::ADJUNTO, 'pagado_por' => 'DEMO Tesorero',
                    'observaciones' => 'DEMO gasto '.$i, 'registrado_por' => $this->usuarios['tesorero']->id]);
                if ($i === 3) $egreso->anular('DEMO: comprobante incorrecto.', $this->usuarios['tesorero']);
            }
        }
    }

    private function otros(): void
    {
        foreach (['asamblea', 'directiva'] as $tipo) {
            for ($i = 1; $i <= 3; $i++) Acta::create(['tipo' => $tipo, 'fecha' => today()->subDays(30 * $i),
                'acuerdos' => 'DEMO '.$tipo.' '.$i.': aprobar actividades comunitarias y revisar gastos.',
                'escaneo_path' => $i === 1 ? null : self::ADJUNTO, 'registrado_por' => $this->usuarios['secretario']->id]);
        }
        foreach (array_keys(Proyecto::ESTADOS) as $estado) {
            for ($i = 1; $i <= 3; $i++) Proyecto::create(['titulo' => 'DEMO '.$estado.' '.$i,
                'organismo' => 'DEMO Fondo comunitario', 'fecha_postulacion' => today()->subDays(40),
                'estado' => $estado, 'monto' => $i === 1 ? null : 500000 * $i,
                'postulado_por' => $this->usuarios['presidente']->name,
                'postulador_id' => $this->usuarios['presidente']->id, 'registrado_por' => $this->usuarios['presidente']->id]);
        }
        // No altera la atención pública que ya haya configurado la directiva.
        if (! HorarioAtencion::exists()) {
            foreach ([2, 4] as $dia) HorarioAtencion::create(['dia_semana' => $dia, 'hora_inicio' => '10:00', 'hora_fin' => '12:00']);
        }
    }
}
