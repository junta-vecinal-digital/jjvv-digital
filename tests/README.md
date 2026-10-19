# Organización de las pruebas

Las pruebas funcionales se agrupan por módulo dentro de `tests/Feature`. Los nombres de archivos describen el comportamiento que cubren; el historial de sprints se mantiene en la documentación del proyecto.

## Carpetas

| Carpeta | Alcance |
| --- | --- |
| `Feature/Auth` | Autenticación, registro, contraseñas y verificación de correo. |
| `Feature/Cuenta` | Perfil y eliminación de la cuenta propia. |
| `Feature/Autorizacion` | Accesos por rol y por estado de la ficha. |
| `Feature/Socios` | Ciclo de vida de fichas y cuentas de ventanilla. |
| `Feature/Certificados` | Solicitud, emisión, descarga y retiro de residencia. |
| `Feature/Agrupaciones` | Representantes y conservación tras la baja. |
| `Feature/Reservas` | Calendario, recurrencias, solicitudes, aportes y finanzas de sede. |
| `Feature/Finanzas` | Ingresos, egresos, devoluciones, anulaciones y garantías de insumos. |
| `Feature/Inventario` | Catálogo, existencias, solicitudes y préstamos históricos. |
| `Feature/Actas` | Registro, consulta y archivos privados. |
| `Feature/Proyectos` | Registro, consulta y postuladores. |
| `Feature/Reportes` | Filtros, permisos y exportaciones. |
| `Feature/Auditoria` | Trazabilidad, filtros y protección de registros. |
| `Feature/Publico` | Acceso a la portada. |
| `Feature/Integracion` | Recorridos y archivos que combinan varios módulos, incluida atención/notificaciones. |
| `Unit` | Pruebas unitarias existentes. |

Los archivos de integración se conservan completos para mantener sus funciones auxiliares, preparación y casos relacionados. No se dividieron ni fusionaron pruebas. Las funciones auxiliares mantienen sus nombres internos; no se encontraron llamadas a ellas desde otros archivos de tests.

## Ejecución desde CMD

Suite completa:

```cmd
php artisan test
```

Solo un módulo:

```cmd
php artisan test tests/Feature/Certificados
```

Solo un archivo:

```cmd
php artisan test tests/Feature/Integracion/AportesCertificadosYComprobantesTest.php
```

`phpunit.xml` descubre las subcarpetas de `tests/Feature` y `tests/Pest.php` les aplica `Tests\TestCase` y `RefreshDatabase`. Se conserva SQLite en memoria y la configuración existente; no se requieren paquetes, migraciones ni cambios en la aplicación para esta reorganización.

El agente no ejecuta tests. El usuario confirmó el 18/10/2026 los tests y la revisión manual de la aplicación tras esta reorganización. Ante cambios posteriores, volver a ejecutar la suite completa; la comparación de contenido y la comprobación de sintaxis no sustituyen esa ejecución.

## Correspondencia de nombres anteriores

Todos los destinos de esta tabla son relativos a `tests/Feature`. Los seis archivos existentes de `Auth` y el ejemplo de `Unit` mantienen sus rutas.

| Nombre anterior | Ruta actual |
| --- | --- |
| `CertificadosYSociosTest.php` | [Integracion/SociosCertificadosYAuditoriaTest.php](Feature/Integracion/SociosCertificadosYAuditoriaTest.php) |
| `CicloDeVidaSocioTest.php` | [Socios/CicloDeVidaSocioTest.php](Feature/Socios/CicloDeVidaSocioTest.php) |
| `ExampleTest.php` | [Publico/PortadaTest.php](Feature/Publico/PortadaTest.php) |
| `PermisosPorRolTest.php` | [Autorizacion/PermisosPorRolTest.php](Feature/Autorizacion/PermisosPorRolTest.php) |
| `ProfileTest.php` | [Cuenta/PerfilTest.php](Feature/Cuenta/PerfilTest.php) |
| `Sprint1BloqueATest.php` | [Integracion/ParametrosEIngresosTest.php](Feature/Integracion/ParametrosEIngresosTest.php) |
| `Sprint1BloqueB1Test.php` | [Certificados/ResidenciaEnLineaTest.php](Feature/Certificados/ResidenciaEnLineaTest.php) |
| `Sprint1BloqueB2Test.php` | [Certificados/ResidenciaEnVentanillaTest.php](Feature/Certificados/ResidenciaEnVentanillaTest.php) |
| `Sprint1BloqueC1Test.php` | [Socios/CuentasDeVentanillaTest.php](Feature/Socios/CuentasDeVentanillaTest.php) |
| `Sprint1BloqueC2Test.php` | [Auditoria/DescargasYMovimientosTest.php](Feature/Auditoria/DescargasYMovimientosTest.php) |
| `Sprint1RevisionTest.php` | [Integracion/InmutabilidadDeRegistrosTest.php](Feature/Integracion/InmutabilidadDeRegistrosTest.php) |
| `Sprint2AuditoriaTest.php` | [Auditoria/ConsultaYProteccionTest.php](Feature/Auditoria/ConsultaYProteccionTest.php) |
| `Sprint2Bloque2aTest.php` | [Finanzas/IngresosYAnulacionesTest.php](Feature/Finanzas/IngresosYAnulacionesTest.php) |
| `Sprint2Bloque2bTest.php` | [Finanzas/EgresosYDevolucionesTest.php](Feature/Finanzas/EgresosYDevolucionesTest.php) |
| `Sprint2Bloque3aTest.php` | [Reservas/CalendarioYRecurrenciasTest.php](Feature/Reservas/CalendarioYRecurrenciasTest.php) |
| `Sprint2Bloque3bTest.php` | [Reservas/SolicitudesYFinanzasDeSedeTest.php](Feature/Reservas/SolicitudesYFinanzasDeSedeTest.php) |
| `Sprint2BloqueGruposTest.php` | [Agrupaciones/RepresentantesTest.php](Feature/Agrupaciones/RepresentantesTest.php) |
| `Sprint2CierreFinancieroTest.php` | [Finanzas/ConsistenciaDelSaldoTest.php](Feature/Finanzas/ConsistenciaDelSaldoTest.php) |
| `Sprint2MejorasBloque3Test.php` | [Integracion/RepresentantesYCatalogoDeActividadesTest.php](Feature/Integracion/RepresentantesYCatalogoDeActividadesTest.php) |
| `Sprint2MejorasBloque4Test.php` | [Integracion/CertificadosYReservasDeVentanillaTest.php](Feature/Integracion/CertificadosYReservasDeVentanillaTest.php) |
| `Sprint2MejorasBloque5Test.php` | [Integracion/AtencionYNotificacionesTest.php](Feature/Integracion/AtencionYNotificacionesTest.php) |
| `Sprint3BloqueActasTest.php` | [Actas/RegistroYConsultaTest.php](Feature/Actas/RegistroYConsultaTest.php) |
| `Sprint3BloqueInventarioTest.php` | [Inventario/ExistenciasYPrestamosHistoricosTest.php](Feature/Inventario/ExistenciasYPrestamosHistoricosTest.php) |
| `Sprint3BloqueProyectosTest.php` | [Proyectos/RegistroYConsultaTest.php](Feature/Proyectos/RegistroYConsultaTest.php) |
| `Sprint3CatalogoInventarioTest.php` | [Inventario/CatalogoYExistenciasTest.php](Feature/Inventario/CatalogoYExistenciasTest.php) |
| `Sprint3CierreIntegracionTest.php` | [Integracion/CicloCompletoDePrestamoTest.php](Feature/Integracion/CicloCompletoDePrestamoTest.php) |
| `Sprint3FinanzasInsumosTest.php` | [Finanzas/AportesYGarantiasDeInsumosTest.php](Feature/Finanzas/AportesYGarantiasDeInsumosTest.php) |
| `Sprint3MejorasInterfazTest.php` | [Integracion/NavegacionYConservacionDeDatosTest.php](Feature/Integracion/NavegacionYConservacionDeDatosTest.php) |
| `Sprint3ReportesTest.php` | [Reportes/ExportacionesYFiltrosTest.php](Feature/Reportes/ExportacionesYFiltrosTest.php) |
| `Sprint3ReservasAporteTest.php` | [Reservas/AportesDeSedeTest.php](Feature/Reservas/AportesDeSedeTest.php) |
| `Sprint3SolicitudesInsumosTest.php` | [Inventario/SolicitudesDeInsumosTest.php](Feature/Inventario/SolicitudesDeInsumosTest.php) |
| `Sprint4MejorasCierreTest.php` | [Integracion/FichasDeDirigentesYSolicitudesTest.php](Feature/Integracion/FichasDeDirigentesYSolicitudesTest.php) |
| `Sprint4AportesYCertificadosTest.php` | [Integracion/AportesCertificadosYComprobantesTest.php](Feature/Integracion/AportesCertificadosYComprobantesTest.php) |
