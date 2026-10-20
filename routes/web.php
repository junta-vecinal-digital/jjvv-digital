<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\CertificadoAdminController;
use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\CitaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SocioController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VerificacionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ParametroController;
use App\Http\Controllers\IngresoController;
use App\Http\Controllers\EgresoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\HorarioFijoController;
use App\Http\Controllers\ArriendoController;
use App\Http\Controllers\ArriendoFinancieroController;
use App\Http\Controllers\HorarioAtencionController;
use App\Http\Controllers\ActaController;
use App\Http\Controllers\ProyectoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\PrestamoController;
use App\Http\Controllers\SolicitudInsumoController;
use App\Http\Controllers\PrestamoFinancieroController;
use App\Http\Controllers\ReporteController;
use App\Models\HorarioAtencion;
use App\Models\Socio;
use App\Models\Certificado;
use Illuminate\Http\Request;

// ---------- Público ----------
Route::get('/', function () {
    return view('welcome', [
        'horariosAtencion' => HorarioAtencion::orderBy('dia_semana')->orderBy('hora_inicio')->get()->groupBy('dia_semana'),
    ]);
});

// Verificación en línea de certificados (sin cuenta)
Route::get('/verificar', [VerificacionController::class, 'formulario'])->name('verificar.formulario');
Route::post('/verificar', [VerificacionController::class, 'buscar'])->middleware('throttle:20,1')->name('verificar.buscar');
Route::get('/verificar/{codigo}', [VerificacionController::class, 'mostrar'])->middleware('throttle:30,1')->name('verificar.mostrar');

// ---------- Cualquier usuario autenticado ----------
Route::get('/dashboard', function (Request $request) {
    $esPresidente = $request->user()->hasRole('presidente');

    return view('dashboard', [
        'sociosPendientes' => $esPresidente ? Socio::where('estado', 'pendiente')->oldest()->limit(5)->get() : collect(),
        'certificadosPendientes' => $esPresidente ? Certificado::where('estado', 'solicitado')->oldest()->limit(5)->get() : collect(),
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Actas: el controlador limita la consulta según rol y estado de la ficha.
Route::middleware('auth')->prefix('actas')->name('actas.')->group(function () {
    Route::get('/', [ActaController::class, 'index'])->name('index');
    Route::get('/{acta}', [ActaController::class, 'show'])->whereNumber('acta')->name('show');
    Route::get('/{acta}/escaneo', [ActaController::class, 'escaneo'])->whereNumber('acta')->name('escaneo');
});

// Proyectos: consulta para toda cuenta autenticada; registro y edición solo del presidente.
Route::middleware('auth')->prefix('proyectos')->name('proyectos.')->group(function () {
    Route::get('/', [ProyectoController::class, 'index'])->name('index');
    Route::get('/{proyecto}', [ProyectoController::class, 'show'])->whereNumber('proyecto')->name('show');
    Route::middleware('role:presidente')->group(function () {
        Route::get('/crear', [ProyectoController::class, 'create'])->name('create');
        Route::post('/', [ProyectoController::class, 'store'])->name('store');
        Route::get('/{proyecto}/editar', [ProyectoController::class, 'edit'])->whereNumber('proyecto')->name('edit');
        Route::put('/{proyecto}', [ProyectoController::class, 'update'])->whereNumber('proyecto')->name('update');
    });
});

// ---------- Vecino ----------
Route::middleware(['auth', 'role:vecino,presidente,secretario,tesorero'])->group(function () {

    // Solicitud de inscripción como socio
    Route::get('/socios/solicitud', [SocioController::class, 'create'])->name('socios.create');
    Route::post('/socios/solicitud', [SocioController::class, 'store'])->name('socios.store');
    Route::put('/socios/solicitud', [SocioController::class, 'reenviar'])->name('socios.reenviar');

    // Reserva de hora para firmar el libro
    Route::get('/mi-ficha/documentos/{tipo}', [SocioController::class, 'documentoPropio'])->name('socios.documento-propio');
    Route::get('/mi-cita', [CitaController::class, 'index'])->name('citas.index');
    Route::post('/mi-cita/{cita}', [CitaController::class, 'reservar'])->name('citas.reservar');
    Route::delete('/mi-cita', [CitaController::class, 'cancelar'])->name('citas.cancelar');

});

// ---------- Socio activo (según la ficha, no según el rol) ----------
    Route::middleware(['auth', 'socio.activo'])->group(function () {
    Route::get('/certificados', [CertificadoController::class, 'index'])->name('certificados.index');
    Route::post('/certificados', [CertificadoController::class, 'store'])->name('certificados.store');
    Route::get('/certificados/{certificado}/pdf', [CertificadoController::class, 'descargar'])
        ->whereNumber('certificado')->name('certificados.descargar');
    });

Route::middleware(['auth', 'role:presidente,secretario,tesorero'])->get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
Route::middleware(['auth', 'role:presidente,tesorero'])->group(function () {
    Route::get('/reportes/finanzas', [ReporteController::class, 'financieros'])->name('reportes.financieros');
    Route::get('/reportes/finanzas/pdf', [ReporteController::class, 'financierosPdf'])->name('reportes.financieros.pdf');
});
Route::middleware(['auth', 'role:presidente,secretario'])->group(function () {
    Route::get('/reportes/socios', [ReporteController::class, 'socios'])->name('reportes.socios');
    Route::get('/reportes/socios/pdf', [ReporteController::class, 'sociosPdf'])->name('reportes.socios.pdf');
    Route::get('/reportes/socios/excel', [ReporteController::class, 'sociosExcel'])->name('reportes.socios.excel');
});

// ---------- Finanzas: presidente y tesorero ----------
Route::middleware(['auth', 'role:presidente,tesorero'])->group(function () {
    Route::get('/finanzas/pendientes', [\App\Http\Controllers\IngresoPendienteController::class, 'index'])->name('finanzas.pendientes.index');
    Route::get('/finanzas/insumos', [PrestamoFinancieroController::class, 'index'])->name('finanzas.insumos.index');
    Route::get('/finanzas/insumos/{solicitud}', [PrestamoFinancieroController::class, 'show'])->whereNumber('solicitud')->name('finanzas.insumos.show');
    Route::post('/finanzas/insumos/{solicitud}/registrar', [PrestamoFinancieroController::class, 'registrar'])->whereNumber('solicitud')->name('finanzas.insumos.registrar');
    Route::post('/finanzas/insumos/{solicitud}/reingresar', [PrestamoFinancieroController::class, 'reingresar'])->whereNumber('solicitud')->name('finanzas.insumos.reingresar');
    Route::get('/finanzas/insumos/{solicitud}/comprobante', [PrestamoFinancieroController::class, 'comprobante'])->whereNumber('solicitud')->name('finanzas.insumos.comprobante');
    Route::get('/finanzas/arriendos', [ArriendoFinancieroController::class, 'index'])->name('finanzas.arriendos.index');
    Route::post('/finanzas/arriendos/{reserva}/registrar', [ArriendoFinancieroController::class, 'registrar'])->whereNumber('reserva')->name('finanzas.arriendos.registrar');
    Route::post('/finanzas/garantias/{garantia}/por-devolver', [ArriendoFinancieroController::class, 'porDevolver'])->whereNumber('garantia')->name('finanzas.garantias.por-devolver');
    Route::post('/finanzas/garantias/{garantia}/devolver', [ArriendoFinancieroController::class, 'devolver'])->whereNumber('garantia')->name('finanzas.garantias.devolver');
    Route::post('/finanzas/garantias/{garantia}/retener', [ArriendoFinancieroController::class, 'retener'])->whereNumber('garantia')->name('finanzas.garantias.retener');
    Route::post('/finanzas/garantias/{garantia}/registrar-retencion', [ArriendoFinancieroController::class, 'registrarRetencion'])->whereNumber('garantia')->name('finanzas.garantias.registrar-retencion');
    Route::get('/ingresos', [IngresoController::class, 'index'])->name('ingresos.index');
    Route::get('/ingresos/crear', [IngresoController::class, 'create'])->name('ingresos.create');
    Route::post('/ingresos', [IngresoController::class, 'store'])->name('ingresos.store');
    Route::get('/ingresos/{ingreso}/comprobante', [IngresoController::class, 'comprobante'])->whereNumber('ingreso')->name('ingresos.comprobante');
    Route::post('/ingresos/{ingreso}/anular', [IngresoController::class, 'anular'])
        ->whereNumber('ingreso')->name('ingresos.anular');
    Route::get('/egresos', [EgresoController::class, 'index'])->name('egresos.index');
    Route::get('/egresos/crear', [EgresoController::class, 'create'])->name('egresos.create');
    Route::post('/egresos', [EgresoController::class, 'store'])->name('egresos.store');
    Route::post('/egresos/{egreso}/anular', [EgresoController::class, 'anular'])
        ->whereNumber('egreso')->name('egresos.anular');
    Route::get('/egresos/{egreso}/comprobante', [EgresoController::class, 'comprobante'])
        ->whereNumber('egreso')->name('egresos.comprobante');
    Route::post('/ingresos/{ingreso}/devolver', [DevolucionController::class, 'store'])
        ->whereNumber('ingreso')->name('ingresos.devolver');
});

// ---------- Calendario: dirigentes y socios activos (el controlador decide qué ve cada uno) ----------
Route::middleware('auth')->get('/calendario', [ReservaController::class, 'index'])->name('calendario.index');
Route::middleware('auth')->prefix('arriendos')->name('arriendos.')->group(function () {
    Route::get('/', [ArriendoController::class, 'index'])->name('index');
    Route::post('/', [ArriendoController::class, 'store'])->name('store');
    Route::get('/{reserva}', [ArriendoController::class, 'show'])->whereNumber('reserva')->name('show');
    Route::get('/{reserva}/comprobante', [ArriendoController::class, 'comprobante'])->whereNumber('reserva')->name('comprobante');
    Route::post('/{reserva}/comprobante', [ArriendoController::class, 'adjuntarComprobante'])->whereNumber('reserva')->name('comprobante.store');
});

Route::middleware('auth')->prefix('solicitudes-insumos')->name('insumos.')->group(function () {
    Route::get('/', [SolicitudInsumoController::class, 'index'])->name('index');
    Route::post('/', [SolicitudInsumoController::class, 'store'])->name('store');
    Route::get('/{solicitud}', [SolicitudInsumoController::class, 'show'])->whereNumber('solicitud')->name('show');
    Route::middleware('role:presidente,secretario')->group(function () {
        Route::post('/{solicitud}/aprobar', [SolicitudInsumoController::class, 'aprobar'])->whereNumber('solicitud')->name('aprobar');
        Route::post('/{solicitud}/entregar', [SolicitudInsumoController::class, 'entregar'])->whereNumber('solicitud')->name('entregar');
        Route::post('/{solicitud}/devolver', [SolicitudInsumoController::class, 'devolver'])->whereNumber('solicitud')->name('devolver');
    });
});

// ---------- Presidente y secretario ----------
Route::middleware(['auth', 'role:presidente,secretario'])->group(function () {

    // Inventario
    Route::get('/socios/archivo', [SocioController::class, 'index'])->name('socios.archivo');
    Route::get('/reservas', [ReservaController::class, 'gestion'])->name('reservas.index');
    Route::get('/inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::post('/inventario', [InventarioController::class, 'store'])->name('inventario.store');
    Route::put('/inventario/{item}', [InventarioController::class, 'update'])->whereNumber('item')->name('inventario.update');
    Route::prefix('inventario/prestamos')->name('inventario.prestamos.')->group(function () {
        Route::get('/', [PrestamoController::class, 'index'])->name('index');
        Route::get('/crear', [PrestamoController::class, 'create'])->name('create');
        Route::post('/', [PrestamoController::class, 'store'])->name('store');
        Route::get('/{prestamo}', [PrestamoController::class, 'show'])->whereNumber('prestamo')->name('show');
        Route::post('/{prestamo}/devolver', [PrestamoController::class, 'devolver'])->whereNumber('prestamo')->name('devolver');
    });

    // Registro de actas
    Route::get('/actas/crear', [ActaController::class, 'create'])->name('actas.create');
    Route::post('/actas', [ActaController::class, 'store'])->name('actas.store');

    // Socios
    Route::prefix('socios')->name('socios.')->group(function () {
        Route::get('/', [SocioController::class, 'index'])->name('index');
        Route::get('/crear', [SocioController::class, 'crear'])->name('crear');
        Route::post('/', [SocioController::class, 'guardar'])->name('guardar');
        Route::get('/{socio}', [SocioController::class, 'show'])->whereNumber('socio')->name('show');
        Route::get('/{socio}/editar', [SocioController::class, 'editar'])->whereNumber('socio')->name('editar');
        Route::put('/{socio}', [SocioController::class, 'actualizar'])->whereNumber('socio')->name('actualizar');
        Route::patch('/{socio}', [SocioController::class, 'update'])->whereNumber('socio')->name('update');
        Route::get('/{socio}/documento/{tipo}', [SocioController::class, 'documento'])->whereNumber('socio')->name('documento');
        Route::post('/{socio}/cuenta', [SocioController::class, 'crearCuenta'])
            ->whereNumber('socio')->name('cuenta');
    });

    // Horarios públicos de atención
    Route::get('/atencion', [HorarioAtencionController::class, 'index'])->name('atencion.index');
    Route::post('/atencion', [HorarioAtencionController::class, 'store'])->name('atencion.store');
    Route::put('/atencion/{horario}', [HorarioAtencionController::class, 'update'])->whereNumber('horario')->name('atencion.update');
    Route::delete('/atencion/{horario}', [HorarioAtencionController::class, 'destroy'])->whereNumber('horario')->name('atencion.destroy');

    // Agenda de firmas
    Route::prefix('agenda')->name('agenda.')->group(function () {
        Route::get('/', [CitaController::class, 'agenda'])->name('index');
        Route::post('/', [CitaController::class, 'store'])->name('store');
        Route::patch('/{cita}/liberar', [CitaController::class, 'liberar'])->name('liberar');
        Route::delete('/{cita}', [CitaController::class, 'destroy'])->name('destroy');
    });

    // Grupos y organizaciones
    Route::prefix('grupos')->name('grupos.')->group(function () {
        Route::get('/', [GrupoController::class, 'index'])->name('index');
        Route::post('/', [GrupoController::class, 'store'])->name('store');
        Route::get('/{grupo}', [GrupoController::class, 'show'])->whereNumber('grupo')->name('show');
        Route::put('/{grupo}', [GrupoController::class, 'update'])->whereNumber('grupo')->name('update');
        Route::post('/{grupo}/representante', [GrupoController::class, 'asignarRepresentante'])
            ->whereNumber('grupo')->name('representante');
        Route::put('/{grupo}/contacto', [GrupoController::class, 'actualizarContacto'])
            ->whereNumber('grupo')->name('contacto');
        Route::post('/{grupo}/horarios', [HorarioFijoController::class, 'store'])
            ->whereNumber('grupo')->name('horarios.store');
        Route::post('/{grupo}/horarios/generar', [HorarioFijoController::class, 'generar'])
            ->whereNumber('grupo')->name('horarios.generar');
        Route::put('/horarios/{horario}', [HorarioFijoController::class, 'update'])
            ->whereNumber('horario')->name('horarios.update');
        Route::delete('/horarios/{horario}', [HorarioFijoController::class, 'destroy'])
            ->whereNumber('horario')->name('horarios.destroy');
    });

    // Certificados
    Route::prefix('admin/certificados')->name('admin.certificados.')->group(function () {
        Route::get('/', [CertificadoAdminController::class, 'index'])->name('index');
        Route::get('/crear', [CertificadoAdminController::class, 'create'])->name('create');
        Route::post('/', [CertificadoAdminController::class, 'store'])->name('store');
        Route::get('/{certificado}/comprobante', [CertificadoAdminController::class, 'comprobante'])
            ->whereNumber('certificado')->name('comprobante');
        Route::get('/{certificado}', [CertificadoAdminController::class, 'show'])->whereNumber('certificado')->name('show');
        Route::post('/{certificado}/emitir', [CertificadoAdminController::class, 'emitir'])->whereNumber('certificado')->name('emitir');
        Route::post('/{certificado}/rechazar', [CertificadoAdminController::class, 'rechazar'])->whereNumber('certificado')->name('rechazar');
        Route::post('/{certificado}/anular', [CertificadoAdminController::class, 'anular'])->whereNumber('certificado')->name('anular');
        Route::get('/{certificado}/pdf', [CertificadoAdminController::class, 'pdf'])->whereNumber('certificado')->name('pdf');
        Route::post('/{certificado}/retirar', [CertificadoAdminController::class, 'retirar'])
            ->whereNumber('certificado')->name('retirar');
    });

    // Calendario: ingreso de reservas
    Route::get('/calendario/imprimir', [ReservaController::class, 'imprimir'])->name('calendario.imprimir');
    Route::post('/calendario/reservas', [ReservaController::class, 'store'])->name('reservas.store');
    Route::post('/calendario/reservas/{reserva}/cancelar', [ReservaController::class, 'cancelar'])->whereNumber('reserva')->name('reservas.cancelar');
    Route::post('/arriendos/{reserva}/confirmar', [ArriendoController::class, 'confirmar'])->whereNumber('reserva')->name('arriendos.confirmar');
    Route::post('/arriendos/{reserva}/aporte', [ArriendoController::class, 'definirAporte'])->whereNumber('reserva')->name('arriendos.aporte');
});

// ---------- Auditoría: solo presidente ----------
Route::middleware(['auth', 'role:presidente'])->get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');

// ---------- Administrador: usuarios y roles ----------
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/parametros', [ParametroController::class, 'index'])->name('parametros.index');
    Route::put('/parametros', [ParametroController::class, 'update'])->name('parametros.update');
    Route::post('/parametros/actividades', [ParametroController::class, 'crearActividad'])->name('parametros.actividades.store');
    Route::post('/parametros/insumos', [ParametroController::class, 'crearInsumo'])->name('parametros.insumos.store');
    Route::put('/parametros/actividades/{tipoActividad}', [ParametroController::class, 'actualizarActividad'])->whereNumber('tipoActividad')->name('parametros.actividades.update');
});

Route::middleware(['auth', 'role:admin'])
    ->prefix('usuarios')->name('usuarios.')->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::get('/crear', [UsuarioController::class, 'create'])->name('create');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::get('/{usuario}/editar', [UsuarioController::class, 'edit'])->name('edit');
        Route::patch('/{usuario}', [UsuarioController::class, 'update'])->name('update');
        Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])->name('destroy');
        Route::post('/{usuario}/baja', [UsuarioController::class, 'bajaDirigente'])->name('baja');
    });

require __DIR__.'/auth.php';
