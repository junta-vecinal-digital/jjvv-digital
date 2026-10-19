# Estado de los sprints

Proyecto estudiantil MVP. [REQUIREMENTS.md](REQUIREMENTS.md) es la fuente de verdad para requisitos y permisos. Cualquier modificación de requisitos necesita autorización explícita para ese cambio.

El detalle de entregas, correcciones, revisiones y confirmaciones anteriores se conserva en [docs/SPRINTS_HISTORIAL.md](docs/SPRINTS_HISTORIAL.md). Este archivo contiene únicamente el resumen vigente.

## Estado general

| Sprint | Estado | Alcance |
| --- | --- | --- |
| 1 — Base del sistema | Completado | Usuarios y roles, inscripción y fichas de socios, citas de firma, certificados, parámetros, ingresos base y auditoría. Mejoras posteriores: formularios por pasos, idioma español, horarios públicos, certificados autónomos/de ventanilla y avisos de pendientes. |
| 2 — Grupos, reservas y finanzas | Revisión de cierre documentada; ampliación financiera comprobada | Grupos, calendario, reservas, finanzas y auditoría comprobados. Cobros y garantías de insumos con tests y funcionamiento confirmados por el usuario. Regresión local final confirmada durante el cierre del sprint 4. |
| 3 — Actas, proyectos, insumos y reportes | Completado; integración comprobada | Actas, Proyectos, catálogo, préstamos, finanzas y Reportes comprobados por el usuario, incluido el test del recorrido conjunto. |
| 4 — Pruebas y correcciones finales | Completado en entorno local — 18/10/2026 | Usuario confirma migración, tests posteriores a los últimos ajustes y revisión manual del aporte $0. Desarrollo local del MVP cerrado; evidencias, concurrencia real y despliegue quedan separados. |

## Reglas vigentes que deben conservarse

- Representantes de grupos: pueden no tener ficha ni cuenta; seleccionar socios/dirigentes o registrar manualmente. Se conservan tras la baja del socio. La etiqueta de representante no concede permisos. Sin representante no se admiten reservas nuevas del grupo.
- Calendario de consulta separado de Reservas; socios activos ven solo ocupación. Presidente/secretario gestionan e imprimen. Parámetros y catálogos son exclusivos del administrador.
- Reservas de sede: aporte definido por presidente/secretario por operación; $0 gratuito. Sin tarifas fijas ni garantías nuevas. Si hay aporte positivo, se exige comprobante y verificación antes de confirmar. Las condiciones, pagos y garantías históricos se conservan.
- Finanzas: garantías separadas de ingresos; solo una retención genera ingreso por su parte retenida. No crear ingresos de $0 ni descontar dos veces una cuota por devolución/anulación.

## Sprint 3: bloques comprobados

| Orden | Bloque | Estado / siguiente trabajo |
| --- | --- | --- |
| — | Actas y Proyectos | Implementados; funcionamiento y tests confirmados previamente. Proyectos usa selector de dirigente y conserva el nombre histórico. |
| — | Catálogo e inventario | Comprobados: 15 tipos en cuatro categorías, ampliables por administrador; cantidad editable y disponibilidad que incluye reservas aprobadas, préstamos abiertos y daños. |
| 1 | Solicitudes de insumos | Funcionamiento manual y tests confirmados por el usuario: solicitudes propias y en ventanilla, representación, varias líneas, contacto y recordatorio de atención. |
| 2 | Aprobación y entrega | Funcionamiento manual y tests confirmados: responsable dirigente seleccionado, monto/garantía globales, aprobación con reserva de todas las líneas y entrega sin doble descuento. Bandejas separadas. |
| 3 | Devoluciones y atrasos | Funcionamiento manual y tests confirmados: devolución completa, dañados fuera de servicio, fecha comprometida y puntos rojos para presidente/secretario. Históricos conservados. |
| 4 | Finanzas de préstamos | Tests y funcionamiento confirmados por el usuario: cobro vinculado, garantía separada, devolución/retención tras devolución de insumos y reingreso sin repetir recepción. Acceso de presidente/tesorero, respaldo privado e historial conservado. |
| 5 | Reportes | Tests y presentación confirmados: finanzas por período/grupo y PDF para presidente/tesorero; socios activos en Excel/PDF para presidente/secretario, sin datos de contacto (RFS-79/80 y aclaraciones del usuario). |
| 6 | Cierre | Revisión de cumplimiento documentada y `Integracion/CicloCompletoDePrestamoTest.php` aprobado por el usuario: solicitud, stock, cobro/garantía, devolución con daños, retención, reporte por grupo y anulación/reingreso. |

**Cierre local registrado — 18/10/2026:** migración, tests posteriores y revisión manual del aporte $0 confirmados por el usuario. No queda un bloque de desarrollo local previsto. Evidencias de entrega aún no producidas; concurrencia real no acreditada; despliegue aplazado.

## Evidencia reciente de comprobación

Tests ejecutados satisfactoriamente por el usuario:

- Calendario y recurrencias: `Reservas/CalendarioYRecurrenciasTest.php`.
- Devoluciones/anulaciones y saldo: `Finanzas/ConsistenciaDelSaldoTest.php`.
- Finanzas de insumos y regresión: `Finanzas/AportesYGarantiasDeInsumosTest.php`, `Inventario/SolicitudesDeInsumosTest.php`, `Reservas/SolicitudesYFinanzasDeSedeTest.php` y `Finanzas/ConsistenciaDelSaldoTest.php`; el usuario confirmó los tests y que todo funciona correctamente.
- Filtros, permisos y trazabilidad: `Auditoria/ConsultaYProteccionTest.php`.
- Reservas con aporte y conservación histórica: `Reservas/SolicitudesYFinanzasDeSedeTest.php`, `Integracion/CertificadosYReservasDeVentanillaTest.php`, `Reservas/AportesDeSedeTest.php`.
- Solicitudes, inventario e históricos: `Inventario/SolicitudesDeInsumosTest.php`, `Inventario/ExistenciasYPrestamosHistoricosTest.php`, `Inventario/CatalogoYExistenciasTest.php`; también confirmación manual del flujo nuevo.
- Reportes: `Reportes/ExportacionesYFiltrosTest.php`; tests y presentación confirmados por el usuario.
- Cierre del sprint 4: el usuario confirma migración, tests posteriores a los últimos ajustes y revisión manual del aporte $0; cierre local registrado el 18/10/2026.

El usuario confirmó también catálogo/inventario y los bloques anteriores; los detalles están en el historial. El usuario confirmó una ejecución conjunta de toda la suite antes de los últimos ajustes de cierre. El usuario confirmó después los tests de los últimos ajustes y la revisión manual del aporte $0. Este cierre no acredita concurrencia real ni despliegue actualizado en Ubuntu, ni equivale a un paquete formal de evidencias.

Actividad de pruebas generales del sprint 4: archivos organizados por módulos, con nombres y referencias actualizados. Guía en [tests/README.md](tests/README.md). Contenido conservado y sintaxis comprobada; el usuario confirmó los tests y la revisión manual de la aplicación tras la reorganización. Actividad validada y cierre local del sprint 4 ratificado.

## Límites y pendientes transversales

- No ejecutar tests desde el agente; los ejecuta el usuario. No instalar dependencias innecesarias.
- Mantener historial y condiciones de operaciones anteriores; no reinterpretarlas automáticamente con las reglas nuevas.
- RFS-81/83 precisados con autorización: usuarios, roles, parámetros y catálogos exclusivos del administrador; auditoría exclusiva del presidente. Revisión final en [docs/REVISION_REQUISITOS.md](docs/REVISION_REQUISITOS.md): hallazgos resueltos con autorización; aportes voluntarios, identidad histórica y comprobantes implementados y con tests posteriores confirmados por el usuario.
- Las migraciones y los assets correspondientes deben estar aplicados al comprobar cambios; no se presume que se hayan desplegado en AWS. El vencimiento en segundo plano requiere scheduler; la consulta de ocupación también excluye pendientes vencidas.
- Ajustes finales autorizados: ficha activa para trámites personales de dirigentes, revisión por otra persona, RUT de representantes/solicitudes, reservas unificadas y mejoras visuales. Requisitos actualizados con autorización específica del usuario; futuros cambios requieren nueva confirmación.
- En futuras entregas, actualizar aquí el estado y los pendientes. Reservar el historial para detalles de implementación, correcciones y evidencia, evitando duplicarlos en este resumen.
