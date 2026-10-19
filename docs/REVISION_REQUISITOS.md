# Revisión de requisitos del MVP

Fecha: 18/10/2026. Fuente: REQUIREMENTS.md, rutas, controladores, modelos, migraciones, vistas y pruebas existentes. Revisión estática: no se ejecutaron tests, operaciones de demostración ni acciones sobre el despliegue. La existencia de un test no acredita que se haya ejecutado satisfactoriamente en esta revisión.

## Resolución posterior autorizada

El usuario resolvió H1–H5: dirigentes con ficha activa para trámites personales; residencia con aporte voluntario configurable como sugerencia y opción $0 sin respaldo; nombre/cargo copiados al emitir; descarga de certificado de socio fuera de auditoría; y archivos privados opcionales en ingresos manuales. Estas decisiones actualizan los requisitos correspondientes. El análisis original siguiente se conserva como antecedente, no como lista vigente de defectos.

Implementación entregada con migración 000014 y Integracion/AportesCertificadosYComprobantesTest.php. El 18/10/2026 el usuario confirmó migración, tests posteriores y revisión manual del aporte $0, y solicitó registrar el cierre local del sprint 4. El aporte elegido se conserva; un importe cero no crea ingresos. Los históricos sin copia de identidad no se completan con un nombre actual supuesto. Se presenta la recepción de montos como aportes voluntarios para la comunidad; garantías y gastos siguen identificados por separado. No se han generado evidencias de demostración ni se ha iniciado el despliegue.

## Precisión autorizada de permisos

Se precisaron la tabla de permisos y RFS-81/83: presidente conserva gestión vecinal, finanzas, actas, proyectos, reportes y auditoría; la administración de usuarios, roles, parámetros y catálogos corresponde exclusivamente al administrador. Las rutas ya aplicaban esta separación. No se cambiaron permisos de aplicación ni otros requisitos durante esta revisión.

## Cruce por bloques

«Implementado» indica correspondencia observada en el código; no sustituye la regresión ni la aceptación manual final.

| Bloque | Requisitos | Resultado de la revisión |
| --- | --- | --- |
| Registro, socios y citas | RFU-01–16; RFS-01–18 | Implementados inscripción, RUT, documentos, residencia, estados, corrección, ventanilla, cuenta vinculada, citas, baja/reactivación y auditoría. RFS-17 conserva una excepción antigua de insumos que contradice RFS-71/82/102; ver H1. |
| Certificados | RFU-17–28 y 65; RFS-19–37 y 90 | Implementados solicitud, pago/revisión, folio anual, verificación pública, PDF, plazos, retiro, anulación y emisión autónoma/de ventanilla. Cumplimiento parcial en conservación del otorgante y coherencia del precio; alcance de auditoría de descargas por precisar. Ver H2–H4. RFU-20 y RFS-33/34 están eliminados. |
| Agrupaciones | RFU-29–30; RFS-38–42 y 94 | Representante único, manual o vinculado, autocompletado, contacto independiente, conservación tras baja, etiqueta informativa y bloqueo sin representante. RUT validado en altas/asignaciones nuevas y conservado en reservas; históricos sin RUT no se completan con datos inventados. |
| Calendario y reservas | RFU-31–39; RFS-43–57 y 61 | Calendario de consulta, gestión unificada, catálogo gratuito, bloques/intervalos, superposición, recurrencias mensuales, edición, impresión, ventanilla, aporte variable, comprobante del titular y vencimiento. La liberación periódica requiere scheduler; consultas también excluyen solicitudes vencidas. Concurrencia real sin acreditar. |
| Finanzas | RFU-40–47; RFS-58–68 y 97 | Ingresos, egresos, clasificación, saldo, anulación sin contramovimiento, devolución propia y exclusión entre anular/devolver. Aportes/cobros vinculados y garantías separadas con retención parcial. El término comprobante en ingresos manuales requiere precisión de alcance; ver H5. |
| Inventario y préstamos | RFU-48–50 y 67; RFS-69–72 y 95–101 | Catálogo, existencias, solicitantes elegibles, agrupación, responsable dirigente, varias líneas, aprobación/reserva, entrega, devolución completa, daños fuera de servicio, atrasos, cobro/garantía e históricos. Los bloqueos están implementados; pruebas reales simultáneas pendientes. |
| Actas | RFU-51–53; RFS-73–75 | Registro de fecha/tipo/acuerdos/escaneo, consulta según ficha o cargo y protección equivalente del archivo privado. |
| Proyectos | RFU-54–55; RFS-76–78 | Registro/edición del presidente, postulador dirigente seleccionado, autor del sistema separado, consulta autenticada y conservación del nombre histórico. El filtro ya no se muestra en el listado. |
| Reportes | RFU-56–57; RFS-79–80 | Finanzas por período/agrupación; Sin agrupación para movimientos sin vínculo. Socios solo activos, sin contactos, en PDF/XLSX, con permisos diferenciados. Aplicadas las aclaraciones del usuario. |
| Auditoría, roles, cuenta y parámetros | RFU-58–64; RFS-81–89 | Rutas por rol, cuenta propia, administración exclusiva, restricción del cambio de rol propio, trazabilidad de cambios/IP y consulta inmutable por presidente. Precisión de permisos autorizada aplicada. Ver H4 respecto de descargas. |
| Atención y ajustes finales | RFU-66; RFS-91–94 y 102–103 | Horarios públicos independientes de citas; recordatorio de firma; puntos rojos; navegación según rol; ficha personal del dirigente revisada por otra persona; fechas españolas con mes/año y cambios de presentación. Los accesos personales de dirigentes están en el menú superior derecho según la última instrucción. La presentación requiere comprobación visual, no solo lectura de Blade/CSS. |

## Hallazgos que quedan abiertos

### H1. Dos textos conservan una excepción ya retirada

El párrafo introductorio de permisos y RFS-17 todavía permiten al dirigente solicitar insumos sin ficha activa. RFS-71/82/102 y el controlador actual exigen ficha activa para solicitudes personales, conservando la gestión propia del cargo.

Recomendación: retirar esa excepción de esos dos textos para que expresen la regla ya acordada. No se modificaron: la autorización de esta revisión corresponde a precisar la exclusividad administrativa del punto RFS-81/83.

### H2. El certificado no conserva por separado la identidad histórica del otorgante

RFS-30 exige guardar el nombre de quien otorga. `Certificado::emitir()` guarda `otorgado_por`, pero no una copia del nombre/cargo al emitir; `resources/views/certificados/pdf.blade.php` lee el nombre y rol actuales de la cuenta. Si esa cuenta se renombra o cambia de rol, un PDF regenerado cambia su identificación; si se elimina, la relación queda vacía.

Recomendación prioritaria: guardar nombre y cargo al emitir y utilizarlos al regenerar el documento. Conservar los certificados anteriores sin inventar identidades históricas. No se implementó en esta revisión.

### H3. El precio de residencia no está limitado a los $1.000 especificados

RFU-17/21 y RFS-19/37 indican $1.000. `ParametroController::update()` permite modificar `certificado_residencia_valor`, incluso a cero, y `Certificado::registrarIngreso()` toma ese valor. El funcionamiento con el valor inicial de $1.000 no demuestra que se impida apartarse del requisito.

Recomendación: conservar $1.000 como monto obligatorio e impedir su edición, o acordar explícitamente un precio configurable y después autorizar la precisión correspondiente en requisitos. No se eligió ni implementó una alternativa.

### H4. La auditoría de descargas solo cubre residencia

RFS-25/85 hablan de descargas de certificados. `CertificadoController::descargar()` registra descarga y auditoría dentro de la condición `tipo === residencia`; las descargas de certificados de socio autónomos no generan esos registros. El código comenta una decisión previa del proyecto que la redacción general no explicita.

Recomendación: precisar si el requisito comprende ambos tipos. Para cumplir su lectura general habría que registrar también las descargas del certificado de socio; si se mantiene la excepción, documentarla con autorización. No se cambió ninguno de los dos comportamientos.

### H5. Alcance del comprobante en ingresos manuales

RFS-58 menciona comprobante y RFS-59 acepta copia o número de transacción. El alta manual de ingresos guarda número de talonario/transacción, pero no ofrece adjuntar una copia; los flujos de sede, certificados, egresos e insumos sí gestionan archivos donde corresponde.

El número exigido satisface la alternativa de RFS-59. No se declara un incumplimiento automático: conviene explicitar si RFS-58 usa comprobante como referencia de pago o exige admitir además un archivo en ingresos manuales. No ampliar la funcionalidad sin resolverlo.

## Pendientes de validación, no funciones nuevas

- Regresión local: el usuario confirmó los tests posteriores a los últimos ajustes al solicitar el cierre del sprint 4. Esta revisión no contiene una ejecución del agente ni un log formal de resultados.
- Comprobación manual: se conservan las confirmaciones previas por bloque y la confirmación final específica del aporte $0. Las capturas, grabaciones y exportaciones de evidencia no se han producido.
- Operaciones simultáneas sobre stock, reservas, folios y devoluciones/anulaciones en una base de prueba del motor que se vaya a utilizar. Los tests secuenciales de SQLite no acreditan concurrencia real.
- Despliegue aplazado por instrucción del usuario. No condiciona realizar la revisión local ni se iniciará en este bloque.

## Ideas de evidencia — solo propuestas

- Una ficha por escenario: requisito(s), rol, datos ficticios, pasos, resultado esperado/obtenido, fecha y versión del proyecto.
- Guardar la salida íntegra de la suite ejecutada por el usuario, identificando versión y entorno. Una captura aislada de «passed» aporta menos contexto que el resultado completo.
- Capturas antes/después para estados, puntos rojos, saldo y existencias; incluir al menos un rechazo por validación y otro por permisos.
- Videos cortos de inscripción/firma, reserva gratuita/con aporte y préstamo con devolución dañada/retención parcial. Cada recorrido puede cubrir varios requisitos sin grabar un video por requisito.
- Conservar ejemplos ficticios de PDF/Excel junto con su filtro y el requisito al que corresponden.
- Para concurrencia, anotar dos operaciones simultáneas y el resultado persistido: una aprobación válida y otra rechazada, sin sobreasignación ni duplicados.

No se crearon capturas, grabaciones, exportaciones de demostración, logs de ejecución ni resultados de prueba como evidencia en esta revisión.
