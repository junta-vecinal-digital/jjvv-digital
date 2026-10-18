# Estado de los sprints

Proyecto estudiantil MVP. [REQUIREMENTS.md](REQUIREMENTS.md) es la fuente de verdad para requisitos y permisos. Cualquier modificación de requisitos necesita autorización explícita para ese cambio.

El detalle de entregas, correcciones, revisiones y confirmaciones anteriores se conserva en [docs/SPRINTS_HISTORIAL.md](docs/SPRINTS_HISTORIAL.md). Este archivo contiene únicamente el resumen vigente.

## Estado general

| Sprint | Estado | Alcance |
| --- | --- | --- |
| 1 — Base del sistema | Completado | Usuarios y roles, inscripción y fichas de socios, citas de firma, certificados, parámetros, ingresos base y auditoría. Mejoras posteriores: formularios por pasos, idioma español, horarios públicos, certificados autónomos/de ventanilla y avisos de pendientes. |
| 2 — Grupos, reservas y finanzas | Revisión de cierre documentada; ampliación financiera comprobada | Grupos, calendario, reservas, finanzas y auditoría comprobados. Cobros y garantías de insumos con tests y funcionamiento confirmados por el usuario. Recorrido integral final pendiente. |
| 3 — Actas, proyectos, insumos y reportes | Completado; integración comprobada | Actas, Proyectos, catálogo, préstamos, finanzas y Reportes comprobados por el usuario, incluido el test del recorrido conjunto. |
| 4 — Pruebas y correcciones finales | En curso | Suite completa aprobada por el usuario antes de las observaciones finales. Ajustes de cierre implementados; pendiente comprobar su regresión y presentación. |

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
| 6 | Cierre | Revisión de cumplimiento documentada y `Sprint3CierreIntegracionTest.php` aprobado por el usuario: solicitud, stock, cobro/garantía, devolución con daños, retención, reporte por grupo y anulación/reingreso. |

**Siguiente paso — sprint 4:** aplicar la migración 000013 y comprobar `Sprint4MejorasCierreTest.php` y la suite completa. Revisar fechas/año, menús, reservas unificadas y regularización de dirigentes según [docs/PRUEBAS_FINALES.md](docs/PRUEBAS_FINALES.md). El agente no ejecuta tests; Ubuntu y concurrencia real permanecen pendientes.

## Evidencia reciente de comprobación

Tests ejecutados satisfactoriamente por el usuario:

- Calendario y recurrencias: `Sprint2Bloque3aTest.php`.
- Devoluciones/anulaciones y saldo: `Sprint2CierreFinancieroTest.php`.
- Finanzas de insumos y regresión: `Sprint3FinanzasInsumosTest.php`, `Sprint3SolicitudesInsumosTest.php`, `Sprint2Bloque3bTest.php` y `Sprint2CierreFinancieroTest.php`; el usuario confirmó los tests y que todo funciona correctamente.
- Filtros, permisos y trazabilidad: `Sprint2AuditoriaTest.php`.
- Reservas con aporte y conservación histórica: `Sprint2Bloque3bTest.php`, `Sprint2MejorasBloque4Test.php`, `Sprint3ReservasAporteTest.php`.
- Solicitudes, inventario e históricos: `Sprint3SolicitudesInsumosTest.php`, `Sprint3BloqueInventarioTest.php`, `Sprint3CatalogoInventarioTest.php`; también confirmación manual del flujo nuevo.
- Reportes: `Sprint3ReportesTest.php`; tests y presentación confirmados por el usuario.

El usuario confirmó también catálogo/inventario y los bloques anteriores; los detalles están en el historial. El usuario confirmó una ejecución conjunta de toda la suite antes de los últimos ajustes de cierre. Estos ajustes requieren nueva regresión; no se acredita concurrencia real ni despliegue actualizado en Ubuntu. La composición visual de impresión requiere revisión manual.

## Límites y pendientes transversales

- No ejecutar tests desde el agente; los ejecuta el usuario. No instalar dependencias innecesarias.
- Mantener historial y condiciones de operaciones anteriores; no reinterpretarlas automáticamente con las reglas nuevas.
- Permisos por aclarar: RFS-81 formula acceso general del presidente, mientras RFS-83 y las rutas reservan usuarios/roles al administrador. No ampliar permisos ni modificar requisitos sin autorización específica. Auditoría sigue exclusiva del presidente (RFS-87).
- Las migraciones y los assets correspondientes deben estar aplicados al comprobar cambios; no se presume que se hayan desplegado en AWS. El vencimiento en segundo plano requiere scheduler; la consulta de ocupación también excluye pendientes vencidas.
- Ajustes finales autorizados: ficha activa para trámites personales de dirigentes, revisión por otra persona, RUT de representantes/solicitudes, reservas unificadas y mejoras visuales. Requisitos actualizados con autorización específica del usuario; futuros cambios requieren nueva confirmación.
- En futuras entregas, actualizar aquí el estado y los pendientes. Reservar el historial para detalles de implementación, correcciones y evidencia, evitando duplicarlos en este resumen.
