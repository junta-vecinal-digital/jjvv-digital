<?php

use App\Models\Auditoria;
use App\Models\Devolucion;
use App\Models\Egreso;
use App\Models\Garantia;
use App\Models\Grupo;
use App\Models\Ingreso;
use App\Models\Reserva;
use App\Models\Socio;
use App\Models\SolicitudInsumo;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    $this->withoutVite();
    $this->travelTo(Carbon::parse('2026-11-02 10:00:00'));
    $this->presidenteReportes = User::factory()->create(['role' => 'presidente']);
    $this->actingAs($this->presidenteReportes);
});

afterEach(function () { $this->travelBack(); });

function ingresoReporte(array $cambios = []): Ingreso
{
    return Ingreso::create(array_replace([
        'concepto' => 'cuota_socios', 'tipo_cuota' => 'ordinaria', 'monto' => 5000,
        'fecha_pago' => '2026-10-01', 'medio_pago' => 'efectivo', 'talonario' => 'T-REPORTE', 'registrado_por' => auth()->id(),
    ], $cambios));
}

function socioReporte(string $nombre, string $estado = 'activo', array $cambios = []): Socio
{
    return Socio::create(array_replace([
        'nombre_completo' => $nombre, 'estado' => $estado,
        'rut' => str_pad((string) (Socio::max('id') + 1), 8, '0', STR_PAD_LEFT).'-K',
        'direccion' => 'Dirección confidencial ZZ', 'telefono' => '999887766', 'email' => 'correo-privado@example.test',
        'residente_desde' => '2020-01-01', 'fecha_ingreso' => '2021-02-03',
    ], $cambios));
}

function datosFinancierosReporte(User $autor): Grupo
{
    $grupo = Grupo::create(['nombre' => 'Taller financiero']);
    $reserva = Reserva::create([
        'tipo' => 'arriendo', 'estado' => 'confirmada', 'inicio' => '2026-10-31 10:00:00', 'fin' => '2026-10-31 12:00:00',
        'motivo' => 'Reserva del grupo', 'grupo_id' => $grupo->id, 'responsable_nombre' => 'Responsable histórico', 'valor_arriendo' => 4000,
    ]);
    $solicitud = SolicitudInsumo::create([
        'estado' => 'devuelto', 'solicitante_nombre' => 'Solicitante histórico', 'telefono_contacto' => '912345678',
        'grupo_id' => $grupo->id, 'grupo_nombre' => $grupo->nombre, 'canal' => 'ventanilla',
        'fecha_comprometida' => '2026-10-31', 'monto' => 7000, 'garantia' => 12000,
        'fecha_entrega' => '2026-10-05', 'fecha_devolucion' => '2026-10-31',
    ]);
    $cuota = ingresoReporte();
    ingresoReporte(['concepto' => 'arriendo', 'tipo_cuota' => null, 'monto' => 4000, 'fecha_pago' => '2026-10-31', 'reserva_id' => $reserva->id]);
    ingresoReporte(['concepto' => 'prestamo_insumos', 'tipo_cuota' => null, 'monto' => 7000, 'fecha_pago' => '2026-10-31', 'solicitud_insumo_id' => $solicitud->id]);
    $garantia = Garantia::create([
        'solicitud_insumo_id' => $solicitud->id, 'monto' => 12000, 'estado' => 'retenida',
        'recibida_en' => now(), 'validado_por' => $autor->id, 'monto_retenido' => 2000, 'monto_devuelto' => 10000,
    ]);
    ingresoReporte(['concepto' => 'otros', 'tipo_cuota' => null, 'monto' => 2000, 'fecha_pago' => '2026-10-31', 'garantia_id' => $garantia->id]);
    $egreso = Egreso::create([
        'concepto' => 'mantencion', 'fecha_pago' => '2026-10-31', 'monto' => 2000, 'numero_transaccion' => 'TRX-REPORTE',
        'pagado_por' => $autor->name, 'comprobante_path' => 'historico.pdf', 'registrado_por' => $autor->id,
    ]);
    $egresoAnulado = $egreso->replicate();
    $egresoAnulado->monto = 77777;
    $egresoAnulado->save();
    $egresoAnulado->anular('Egreso excluido', $autor);
    Devolucion::create(['ingreso_id' => $cuota->id, 'fecha_devolucion' => '2026-10-31', 'monto' => 5000, 'motivo' => 'Cuota devuelta', 'registrado_por' => $autor->id]);
    ingresoReporte(['monto' => 99999, 'fecha_pago' => '2026-09-30']);
    ingresoReporte(['monto' => 66666, 'fecha_pago' => '2026-11-01']);
    $anulado = ingresoReporte(['monto' => 88888, 'fecha_pago' => '2026-10-31']);
    $anulado->anular('Movimiento excluido', $autor);
    return $grupo;
}

test('reporte financiero incluye extremos del período resta devoluciones y no duplica garantías ni reservas', function () {
    datosFinancierosReporte($this->presidenteReportes);
    $antes = Auditoria::count();
    $this->get(route('reportes.financieros', ['desde' => '2026-10-01', 'hasta' => '2026-10-31']))->assertOk()
        ->assertViewHas('totales', ['ingresos' => 18000, 'egresos' => 2000, 'devoluciones' => 5000, 'neto' => 11000])
        ->assertViewHas('movimientos', fn ($filas) => $filas->count() === 6)
        ->assertViewHas('reservas', fn ($reservas) => $reservas->count() === 1);
    expect(Auditoria::count())->toBe($antes);
});

test('filtra movimientos por grupo incluyendo préstamos y retenciones y separa sin agrupación', function (bool $sinGrupo) {
    $grupo = datosFinancierosReporte($this->presidenteReportes);
    $totales = $sinGrupo ? ['ingresos' => 5000, 'egresos' => 2000, 'devoluciones' => 5000, 'neto' => -2000]
        : ['ingresos' => 13000, 'egresos' => 0, 'devoluciones' => 0, 'neto' => 13000];
    $this->get(route('reportes.financieros', ['desde' => '2026-10-01', 'hasta' => '2026-10-31', 'grupo_id' => $sinGrupo ? 'sin_grupo' : $grupo->id]))
        ->assertOk()->assertViewHas('totales', $totales)
        ->assertViewHas('reservas', fn ($reservas) => $reservas->count() === ($sinGrupo ? 0 : 1));
})->with([true, false]);

test('el concepto aporte de grupos no inventa una agrupación sin vínculo guardado', function () {
    $grupo = Grupo::create(['nombre' => 'Grupo mencionado']);
    ingresoReporte(['concepto' => 'aporte_grupos', 'observaciones' => $grupo->nombre]);
    $this->get(route('reportes.financieros', ['desde' => '2026-10-01', 'hasta' => '2026-10-31', 'grupo_id' => $grupo->id]))
        ->assertOk()->assertViewHas('movimientos', fn ($filas) => $filas->isEmpty());
});

test('consulta y PDF financieros rechazan filtros inválidos', function (array $cambios, string $campo) {
    $filtros = array_replace(['desde' => '2026-10-01', 'hasta' => '2026-10-31'], $cambios);
    foreach (['reportes.financieros', 'reportes.financieros.pdf'] as $ruta) {
        $this->from(route('reportes.financieros'))->get(route($ruta, $filtros))->assertSessionHasErrors($campo);
    }
})->with([
    [['desde' => 'fecha inválida'], 'desde'], [['hasta' => '2026-09-30'], 'hasta'],
    [['grupo_id' => 'otro'], 'grupo_id'], [['grupo_id' => 999999], 'grupo_id'],
]);

test('el listado solo muestra activos y nunca incluye columnas de contacto aunque se envíe otro estado', function () {
    socioReporte('Socio Activo');
    socioReporte('Socio Inactivo', 'inactivo');
    socioReporte('Vecino Pendiente', 'pendiente');
    $this->get(route('reportes.socios', ['estado' => 'inactivo']))->assertOk()
        ->assertSee('Socio Activo')->assertDontSee('Socio Inactivo')->assertDontSee('Vecino Pendiente')
        ->assertDontSee('Dirección confidencial ZZ')->assertDontSee('999887766')->assertDontSee('correo-privado@example.test')
        ->assertViewHas('socios', fn ($lista) => $lista->total() === 1 && ! array_key_exists('telefono', $lista->first()->getAttributes()));
});

test('Excel contiene todos los resultados filtrados y celdas de texto válidas sin datos de contacto', function () {
    for ($i = 0; $i < 27; $i++) socioReporte('Exportar '.str_pad((string) $i, 2, '0', STR_PAD_LEFT));
    socioReporte('=2+2 & <Exportar>');
    socioReporte('Fuera de búsqueda');
    socioReporte('Exportar inactivo', 'inactivo');
    $this->get(route('reportes.socios', ['buscar' => 'Exportar']))->assertOk()
        ->assertViewHas('socios', fn ($lista) => $lista->total() === 28 && $lista->count() === 25);
    $respuesta = $this->get(route('reportes.socios.excel', ['buscar' => 'Exportar']))->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($respuesta->headers->get('Cache-Control'))->toContain('private')->not->toContain('public');
    $ruta = $respuesta->baseResponse->getFile()->getPathname();
    $zip = new ZipArchive;
    $abierto = false;
    try {
        expect($zip->open($ruta))->toBeTrue();
        $abierto = true;
        foreach (['[Content_Types].xml', '_rels/.rels', 'xl/workbook.xml', 'xl/_rels/workbook.xml.rels', 'xl/worksheets/sheet1.xml'] as $parte) {
            $xml = $zip->getFromName($parte);
            expect($xml)->not->toBeFalse();
            $documento = new DOMDocument;
            expect($documento->loadXML($xml))->toBeTrue();
        }
        $hoja = $zip->getFromName('xl/worksheets/sheet1.xml');
        $documento = new DOMDocument;
        $documento->loadXML($hoja);
        expect($documento->getElementsByTagName('row')->length)->toBe(29)
            ->and($documento->getElementsByTagName('f')->length)->toBe(0)
            ->and($documento->textContent)->toContain('=2+2 & <Exportar>', '03/02/2021')
            ->not->toContain('inactivo', 'Fuera de búsqueda', 'Dirección confidencial ZZ', '999887766', 'correo-privado@example.test');
        foreach ($documento->getElementsByTagName('c') as $celda) expect($celda->getAttribute('t'))->toBe('inlineStr');
    } finally {
        if ($abierto) $zip->close();
        if (is_file($ruta)) unlink($ruta);
    }
});

test('PDFs se descargan como archivos PDF con acceso autorizado', function (string $tipo) {
    socioReporte('Socio PDF');
    datosFinancierosReporte($this->presidenteReportes);
    $respuesta = $this->get(route('reportes.'.$tipo.'.pdf', $tipo === 'financieros' ? ['desde' => '2026-10-01', 'hasta' => '2026-10-31'] : []))
        ->assertOk()->assertHeader('Content-Type', 'application/pdf');
    expect($respuesta->getContent())->toStartWith('%PDF-')->and($respuesta->headers->get('Content-Disposition'))->toContain('attachment');
})->with(['socios', 'financieros']);

test('permisos de reportes distinguen finanzas socios y roles sin acceso', function (string $rol, bool $finanzas, bool $socios) {
    $this->actingAs(User::factory()->create(['role' => $rol]));
    $respuesta = $this->get(route('reportes.index'));
    if ($finanzas || $socios) $respuesta->assertOk(); else $respuesta->assertForbidden();
    $respuesta = $this->get(route('reportes.financieros'));
    if ($finanzas) $respuesta->assertOk(); else $respuesta->assertForbidden();
    if (! $finanzas) $this->get(route('reportes.financieros.pdf'))->assertForbidden();
    $respuesta = $this->get(route('reportes.socios'));
    if ($socios) $respuesta->assertOk(); else {
        $respuesta->assertForbidden();
        $this->get(route('reportes.socios.pdf'))->assertForbidden();
        $this->get(route('reportes.socios.excel'))->assertForbidden();
    }
})->with([['presidente', true, true], ['tesorero', true, false], ['secretario', false, true], ['admin', false, false], ['vecino', false, false]]);

test('reportes y exportaciones requieren iniciar sesión', function () {
    auth()->logout();
    foreach (['index', 'financieros', 'financieros.pdf', 'socios', 'socios.pdf', 'socios.excel'] as $ruta) {
        $this->get(route('reportes.'.$ruta))->assertRedirect(route('login'));
    }
});
