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
- Reservas de sede: aporte definido por presidente/secretario por operación; $0 gratuito. Sin tarifas fijas; garantía opcional por operación. Si hay aporte o garantía positivos, se exige comprobante del total y verificación antes de confirmar. Las condiciones, pagos y garantías históricos se conservan.
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

Ajuste posterior solicitado — 18/10/2026: navegación contextual y nombres más claros en Reservas, registro en ventanilla y autocompletado del representante por vínculo o RUT válido. Tests confirmados por el usuario; requisitos sin cambios.

Optimización visual, etapas 1 a 3 — 18/10/2026: accesos por cargo en el panel, navegación destacada y encabezados uniformes en gestiones frecuentes. Compilación comprobada; el usuario da conformidad inicial y autoriza continuar. Se mantienen módulos y permisos.

Optimización visual, continuación — 18/10/2026: estados destacados en reservas e insumos, orientación del siguiente paso en detalles, avance numérico y botones adaptables en formularios, mejoras de tablas, foco y avisos accesibles. Pendiente de tests y revisión visual del usuario, incluidos zoom y ventanas pequeñas.

Correcciones autorizadas — 19/10/2026: Inventario independiente con existencias editables e historial de consulta; actividades y campo En representación de ajustados; solicitudes de insumos de cualquier vecino en ventanilla; bajas de dirigentes y eliminación de cuentas restringidas; horarios de retiro ocultos para presidente/secretario. REQUIREMENTS.md actualizado con autorización específica. Migración 000015 y pruebas de regresión preparadas; aplicación de migración, tests y comprobación manual pendientes del usuario.

Ajustes autorizados — 19/10/2026: textos simplificados, menú Inventario con Registrar insumo e Historial de préstamos separados; responsable automático presidente/secretario con nombre y cargo históricos; garantía opcional de sede y bandeja Finanzas → Ingresos por aprobar. REQUIREMENTS.md actualizado con autorización específica; se corrige la numeración duplicada del requisito de actividades a RFS-106. Migración 000016 y regresión Finanzas/PendientesYGarantiasDeSedeTest.php preparadas. Pendientes del usuario: migración, tests y revisión manual. Se conservan operaciones y garantías históricas, y la garantía no forma parte del saldo de ingresos.

Diseño aprobado, primera implementación — 19/10/2026: base compartida en modo claro con verde sobrio, superficies neutras y jerarquía tipográfica; Reservas de sede y Solicitudes de insumos con detalles y operaciones desplegables en las filas. Finanzas agrupa Ingresos y Egresos, incorpora Ingresos por aprobar dentro de Ingresos y reúne las garantías de sede e insumos en Registro garantías. Los movimientos anulados vinculados a una operación permiten reingresar su aporte o retención desde el detalle, con las operaciones y bloqueos existentes. Se conservan datos, paginación, respaldos privados, catálogos, parámetros, permisos y reglas financieras. Los formularios distinguen la operación y recuperan su detalle y valores tras errores. Las rutas anteriores de listados financieros redirigen a las vistas agrupadas.

El usuario autorizó expresamente estas diferencias de interfaz respecto de RFS-92/108, manteniendo REQUIREMENTS.md sin cambios. La base visual también se comparte con los demás cargos, sin ampliar sus permisos. No se requieren migraciones nuevas para este diseño; siguen pendientes las migraciones anteriores indicadas arriba. Compilación de recursos y plantillas y revisión de sintaxis realizadas; no se ejecutaron tests. Pendientes del usuario: revisión visual en escritorio y ventanas pequeñas, errores de formularios en filas concretas, operaciones de reservas/préstamos y recorrido financiero (registro, anulación, reingreso y resolución de garantías).

Reinicio local autorizado — 19/10/2026: se vació la base SQLite de desarrollo y se aplicaron las 37 migraciones desde cero, sin cargar usuarios de ejemplo. Se conservó únicamente la cuenta administradora existente (admin@jjvv.cl), con su contraseña, y se reiniciaron parámetros y catálogos a los valores de las migraciones. Todas las tablas operativas y de auditoría quedaron vacías, incluidas existencias, horarios, sesiones y demás cuentas. Se eliminaron 22 adjuntos de desarrollo de las carpetas privadas de socios y comprobantes. Se comprobaron conteos y conservación de la credencial; no se ejecutaron tests. Esta limpieza sustituye los pendientes de aplicación de las migraciones anteriores en este entorno local, sin acreditar despliegue externo.

Alcance de diseño precisado por el usuario: completar todo el prototipo para presidente, administrador y pantallas públicas; conservar permisos e inscripción conforme a RFS-81/82/83/102 y fechas en español conforme a RFS-103 con el estilo del prototipo. El resto de diferencias de interfaz señaladas en la comparación no condicionará la fidelidad al prototipo. REQUIREMENTS.md permanece sin cambios. La limpieza es puntual: los datos que el usuario registre desde ahora se conservarán normalmente. Los bloques de diseño presentados se implementaron en la continuación indicada a continuación.


Diseño completo aprobado, implementación — 19/10/2026: arquitectura lateral por módulos y menú Mi cuenta; Socios activos como entrada, Pendientes unificada y Archivo separado; formularios de sede con Actividad/Solicitante condicionado y autocompletado desde registros reales. Listados con detalles y edición en fila para socios, certificados, agrupaciones, inventario, reservas, finanzas, actas, proyectos, horarios, usuarios y auditoría. Recuperación de valores e identificación de la operación al volver de errores. Altas por etapas y resumen donde corresponde; calendario semanal con días adaptados a ventanas pequeñas, selección de reservas y horarios fijos diferenciados. Paneles de presidente y administrador, reportes agrupados, administración por pestañas, ficha y certificados personales, perfil, portada, verificación pública y pantallas de acceso adaptados al diseño compartido. Se mantienen las rutas de documentos privados, acciones reales, paginación y controles de acceso. No se modificó REQUIREMENTS.md ni se volvió a limpiar la base.

Comprobaciones de esta implementación: compilación de recursos, compilación de plantillas y revisión estática de sintaxis PHP y JavaScript; no se ejecutaron tests. Pendiente del usuario: revisión visual y funcional de ambos perfiles y pantallas públicas, incluyendo formularios con errores, autocompletado, desplegables, documentos y recorridos financieros. Esta revisión estática no acredita el funcionamiento completo en navegador ni el despliegue externo.

Adaptación de la suite autorizada — 19/10/2026: expectativas actualizadas para navegación, formularios de alta, avisos de pendientes, horarios públicos, pestañas de Parámetros y redirecciones de Reportes/Proyectos/Finanzas. Los pendientes financieros se consultan desde Ingresos y las garantías de sede e insumos desde su registro conjunto. Se conserva la cobertura de permisos y reglas de negocio y se añaden casos de archivo/pendientes de socios, garantías agrupadas y recuperación de errores en una fila de proyectos sin mezclar valores. REQUIREMENTS.md y código de aplicación sin cambios en esta tarea. Suite sin ejecutar por el agente; ejecución y comprobación funcional pendientes del usuario. Guía en tests/README.md.

Corrección autorizada tras ejecución del usuario — 19/10/2026: el caso de recuperación de errores en Proyectos detectó que el listado mostraba literalmente la inclusión del detalle, sin renderizar su formulario. Se separó la directiva de inclusión del cierre de x-slot; compilación y sintaxis de listado, detalle y edición comprobadas sin ejecutar tests. Se conserva el test de regresión. Pendiente del usuario: repetir el caso y comprobar la edición en fila. REQUIREMENTS.md sin cambios.
