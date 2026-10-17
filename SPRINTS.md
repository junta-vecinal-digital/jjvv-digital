## Plan de sprints

Los requisitos de REQUIREMENTS.md fueron organizados según el método de prioridad MosCow para definir los sprints del proyecto.

### [COMPLETADO] Sprint 1: MUST HAVE

Entregables:

- Configuración del entorno web y base de datos central.
- Roles y permisos (Administrador, Presidente, Secretario, Tesorero, Vecino). “Socio” se deduce de la ficha activa.
- Parámetros configurables del sistema (horario de la sede, vigencia del certificado, plazos y valores).
- Registro de vecinos y solicitud de inscripción, con rechazo con motivo y reapertura para corregirla.
- Agenda de citas de firma del libro de socios.
- Mantenedor de socios: registro en ventanilla, búsqueda, edición, baja y reactivación, y creación de cuenta para socios registrados en ventanilla.
- Certificados de residencia: solicitud en línea, emisión con folio y PDF, descarga con plazo, retiro presencial, rechazo, anulación, certificado para no socios en ventanilla y validación pública.
- Registro base de ingresos, para guardar el pago del certificado.
- Auditoría de socios, certificados y usuarios.
- Gestión de usuarios y roles por el administrador, y eliminación de la propia cuenta.

### [IMPLEMENTADO; CIERRE CON OBSERVACIONES] Sprint 2: SHOULD HAVE

Entregables:

- Grupos y organizaciones, con su representante vigente.
- Calendario de espacios: reservas, horarios fijos de grupos, arriendos con confirmación de pago y garantía, y horario imprimible.
- Módulo financiero completo: ingresos (con cuotas ordinarias y extraordinarias), garantías de arriendo, egresos, devoluciones, anulaciones y saldo de caja.
- Auditoría de finanzas y consulta de auditoría con filtros por fecha, autor y tipo de registro.

### [Incompleto] Sprint 3: COULD HAVE

Entregables:

- Actas de asamblea y de directiva, con acceso según rol.
- Proyectos postulados y adjudicados, visibles para todo usuario con sesión iniciada.
- Inventario y préstamos de insumos.
- Reportes: financieros y listado de socios en Excel y PDF.

#### [Incompleto] Sprint 4: TESTING & BUFFER

Objetivo: Pruebas, corrección de errores (bugs).

## Avance actual

La base del sprint 1 está implementada y se continúa con el sprint 2. Los bloques 1, 2a y 2b tienen implementación; el bloque 3a mantiene pendiente el cierre documentado de sus comprobaciones. El usuario confirmó la comprobación manual y la ejecución satisfactoria de los tests del bloque 3b. El usuario confirmó también el funcionamiento del horario imprimible; el bloque 4 queda completado. La existencia de código o de archivos de prueba no implica que las pruebas hayan pasado.

### Hallazgos:

- user_id tiene nullOnDelete. Esto podría afectar la trazabilidad, pero no se abordará.
- Trazabilidad financiera implementada: el filtro “finanzas” incluye ingresos, egresos, devoluciones y garantías, con filtros por fecha, autor y categoría.
- Calendario corregido, pendiente de comprobación manual: cada celda calcula la superposición con los intervalos reales de reservas y horarios fijos, aunque cambie la duración o la apertura de los bloques.
- Certificados de ventanilla corregidos, pendientes de comprobación manual: al seleccionar un socio se guarda su cuenta vinculada. Los certificados anteriores con socio_id y user_id vacío se incluyen en la consulta de su titular, sin renovar el plazo de descarga ni modificar esos registros.
- Permisos por aclarar: la matriz indica acceso del presidente a todos los módulos, mientras que rutas y pruebas reservan usuarios y roles al administrador. No se han cambiado permisos.

### Bloque 1 [Completado]: 

grupos y representante (RFS-38 a RFS-42, RFU-29 y RFU-30). Crear y editar grupos con horarios de uso, asignar un representante que sea socio activo con cuenta, y copiar sus datos de contacto al grupo. Cuando un socio se da de baja, su grupo queda sin representante y no acepta reservas nuevas. Esto se conecta con la baja que ya existe en SocioController.

### Bloque 2a [Completado]: ingresos, cuotas y saldo.

- Ampliar los conceptos de ingreso con “cuota de socios” y “aporte de grupos”, además de los actuales “certificados” y “otros” (RFS-58). El concepto “arriendo” llega en el bloque 3b.
- Clasificar cada cuota o aporte como ordinario o extraordinario (RFS-60).
- Anular un ingreso con motivo obligatorio, sin borrarlo (RFS-67).
- Saldo de caja: ingresos menos egresos menos devoluciones, sin contar lo anulado (RFS-68).

### Bloque 2b [Completado]: egresos y devoluciones.

- Egresos con fecha, monto, concepto (servicios básicos o mantención), número de transacción, copia del comprobante y quién pagó (RFS-65).
- Devolución de una cuota con motivo obligatorio, que descuenta del saldo de caja (RFS-66).
Anulación de egresos, con el mismo criterio que ingresos.
- Anulación: marcar el registro con fecha, motivo y autor, sin crear un movimiento contrario. 
- Devolución de cuota: guardarla como movimiento propio, ligado a la cuota original, y no como un ingreso con monto negativo.

### Bloque 3a [Implementado; pendiente de comprobación manual]:

calendario y reservas (RFS-43, RFS-44, RFS-47 a RFS-49). Modelo de reserva con día, hora, motivo, tipo, quién reserva y quién la ingresa. Calendario digital, horarios fijos de grupo que generan reservas cada mes, y bloqueo de superposiciones.

Implementado, pendiente de comprobación manual:

- Calendario semanal: socios activos ven libre/ocupado; presidente y secretario ven el detalle de reservas.
- Las nuevas reservas de grupo guardan al representante vigente y al usuario que las ingresa.
- Horarios fijos editables y generación mensual sin duplicar reservas ya generadas.
- Validación de superposiciones entre reservas y horarios fijos, con escrituras serializadas para solicitudes simultáneas.
- Editar o eliminar un horario fijo mantiene las reservas ya generadas.
- Ingreso manual con tres actividades fijas: de grupo, particular gratuita y de la junta. El motivo se completa automáticamente; la actividad particular identifica a un socio activo responsable.

### Simplificación de formularios (pendiente de comprobación manual)

- Asistentes por pasos en grupos, socios, certificados, ingresos, egresos y usuarios.
- Datos del representante y del socio reutilizados, destinatarios anteriores sugeridos y responsables de pago seleccionables con opción de otra persona.
- Fechas sugeridas, campos de pago según el medio elegido, formato de RUT y generación opcional de contraseñas.
- Se mantienen los motivos de rechazo, anulación y devolución y las verificaciones manuales de certificados.
- El módulo de parámetros se mantiene fuera de estos cambios. Se deja para más adelante evaluar un catálogo editable de tipos de actividad.

### Bloque 3b [Completado; comprobación manual y tests confirmados por el usuario]:

arriendos y garantía (RFS-50 a RFS-54, RFS-61 a RFS-64). Solicitud de arriendo por socios activos, estado pendiente que bloquea el horario, liberación automática a las 48 horas, validación de pago y garantía, cancelación, y garantía como registro separado con sus estados. Aquí se usan los parámetros de arriendo que ya existen.

Primera entrega implementada:

- Solicitud por pasos, propia o en ventanilla por presidente/secretario, con socio activo responsable y grupo opcional con representante vigente.
- Un comprobante privado por el total de arriendo y garantía. Los montos y el vencimiento se guardan con los valores vigentes al solicitar.
- Reserva pendiente que bloquea el horario; vencimiento configurable que libera la ocupación y comando programado cada minuto para actualizar el estado.
- Presidente/secretario verifica ambos pagos y confirma. La garantía recibida se registra separada de los ingresos y se audita.
- Cancelación de reservas por presidente/secretario: libera el horario y deja una garantía recibida como por devolver. Cancelar una ocurrencia de horario fijo libera solo esa fecha.
- Aplicar la nueva migración antes de usar calendario/arriendos. Para el vencimiento en segundo plano en desarrollo: `php artisan schedule:work`, en una consola adicional.

Segunda entrega implementada y comprobada por el usuario:

- Registro financiero por presidente/tesorero desde un arriendo validado, con monto y comprobante reutilizados. Impide duplicar ingresos vigentes y permite volver a registrar tras anulación.
- Garantía registrada por separado; devolución total con fecha y autor, sin afectar nuevamente el saldo de caja. Estado por devolver disponible para finanzas.
- Acceso del tesorero a arriendos validados desde finanzas, sin permisos para confirmar o cancelar reservas.
- El usuario confirmó funcionamiento manual y ejecución satisfactoria del archivo de tests del bloque 3b. El agente no ejecutó tests.
- Retención parcial o total con motivo y monto manual entre $1 y el total de la garantía. Registra la devolución del resto en la misma operación y crea un ingreso solo por la parte retenida. Guarda fecha y autor y bloquea una segunda resolución.
- Anular el ingreso de una retención conserva la resolución de la garantía; desde el detalle se puede volver a registrar ese mismo ingreso sin repetir la devolución. Se impiden ingresos vigentes duplicados mediante la misma serialización de escrituras.
- Archivo `tests/Feature/Sprint2Bloque3bTest.php`: solicitudes, representación, comprobante privado, superposición, vencimiento, confirmación, cancelación, permisos, registro financiero, devoluciones, retención parcial/total, auditoría y anulación/reingreso. Ejecución satisfactoria confirmada por el usuario. Los envíos repetidos se comprueban secuencialmente; no se simula concurrencia real.

### Bloque 4 [Completado; funcionamiento del horario imprimible confirmado por el usuario]:

trazabilidad financiera (RFU-60, RFS-85 y RFS-87) y horario imprimible (RFU-38, RFS-57). La consulta de auditoría financiera incluye ingresos, egresos, devoluciones y garantías.

- Presidente y secretario pueden elegir una fecha desde calendario e imprimir la semana o el mes correspondiente.
- Vista por día con fecha, horario, actividad y estado; incluye reservas vigentes y horarios fijos aún sin generar. Excluye reservas canceladas/vencidas y respeta las ocurrencias fijas canceladas.
- Formato A4, encabezados de tabla repetidos y controles ocultos al imprimir; permite guardar PDF con el navegador, sin nuevas dependencias.
- El usuario confirmó que el horario imprimible funciona correctamente. No se han ejecutado tests adicionales por parte del agente.

## Próximos pasos

1. Resolver las observaciones de cierre descritas en la revisión del sprint 2, cuando el usuario lo indique.
2. Conservar la validación manual y los tests del bloque 3b confirmados por el usuario, y la validación manual del horario imprimible.
3. Completar la evidencia de validación de calendario recurrente y filtros de auditoría; no asumir que los archivos de tests existentes ya fueron ejecutados.
4. Esperar la siguiente instrucción del usuario. No iniciar el sprint 3 con esta revisión.

## Revisión de cierre del sprint 2 — 16-10-2026

Revisión estática de REQUIREMENTS.md, rutas, controladores, modelos, migraciones, vistas y archivos de tests. No se ejecutaron tests ni se modificó lógica de aplicación. Se incorporan las confirmaciones del usuario sobre funcionamiento manual y tests del bloque 3b y sobre impresión. Resultado: todos los entregables tienen implementación; se identificaron observaciones que impiden declarar conformidad completa sin matices.

### Cruce con requisitos

| Entregable | Requisitos | Evidencia de implementación | Resultado |
| --- | --- | --- | --- |
| Grupos, representante y contacto | RFU-29/30; RFS-38 a 42 | GrupoController, Grupo, baja en SocioController; controles de representante en reservas, arriendos y generación mensual. Los horarios estructurados sustituyen el antiguo texto libre. | Implementado. Tests de grupos disponibles; no se afirma una nueva ejecución. |
| Calendario y datos de reserva | RFU-31/32/33; RFS-43/44/55/56 | ReservaController, Reserva y calendario/index: socio ve libre/ocupado; presidente/secretario ve detalles; responsable y autor registrados. | Implementado. La actividad particular gratuita es la ampliación ya acordada de RFS-44. |
| Bloques y superposiciones | RFS-45/46/48; dependencia RFU-39, RFS-88 | Reserva::errorDeBloques/errorDeConflicto, HorarioFijo::errorDeConflicto y parámetros. Escrituras de calendario serializadas. | Implementado. Falta cobertura automática específica del conjunto de casos de bloques y recurrencias. |
| Recurrencia mensual y edición | RFU-34; RFS-47/49 | HorarioFijoController: creación, edición y generación de reservas confirmadas; evita duplicados y conserva reservas previas al editar/eliminar horarios. | Implementado. No existe un archivo dedicado de tests del bloque 3a. |
| Solicitud, validación, vencimiento y cancelación | RFU-35/36/37; RFS-50 a 54 | ArriendoController, ReservaController::cancelar, Reserva::ocupan/liberarVencidos y routes/console.php. | Implementado y validado por el usuario en bloque 3b. Actualización de estados en segundo plano requiere scheduler; la ocupación excluye pendientes vencidas incluso sin él. |
| Horario imprimible | RFU-38; RFS-57 | ReservaController::imprimir, calendario/imprimir y ruta protegida para presidente/secretario. | Implementado; funcionamiento confirmado por el usuario. |
| Ingresos y clasificación | RFU-40/41/47; RFS-58/59/60/68 | IngresoController, Ingreso, registro de arriendos y dependencia de certificados: talonario o transacción/comprobante vinculado; clasificación de cuotas/aportes; saldo excluye anulados. | Implementado. La garantía recibida se mantiene fuera del saldo de ingresos. |
| Garantías y retenciones | RFU-42/43; RFS-61 a 64 | ArriendoFinancieroController y Garantia: montos guardados al solicitar, registro separado, devolución, retención parcial/total e ingreso vinculado por lo retenido. | Implementado y validado por el usuario. Contradicción de RFS-64 aclarada con confirmación explícita del usuario; ver observación 2. |
| Egresos, devolución y anulación | RFU-44/45/46; RFS-65 a 68 | EgresoController, DevolucionController, IngresoController y modelos: motivo, autor, fecha, archivos privados y devoluciones propias ligadas a cuota. | Implementado. Corrección de concurrencia aplicada después de la revisión; pendiente de validación por el usuario. |
| Auditoría financiera y permisos | RFU-60; RFS-85/86/87; dependencia RFS-81/82 | Auditable en ingresos, egresos, devoluciones y garantías; Auditoria::MODELOS; filtros por fecha/autor/tipo; ruta exclusiva del presidente. Finanzas para presidente/tesorero. | Implementado. Tests parciales de auditoría en sprint 1, 2a, 2b y 3b; no se acredita una nueva regresión integral. |

### Observaciones de cierre

1. **Corrección aplicada tras la revisión — carrera entre devolver y anular una cuota (RFS-66/67/68).** El código anterior comprobaba la posibilidad de devolver/anular sin una transacción y bloqueo compartidos. Dos solicitudes simultáneas podían dejar una cuota devuelta y anulada, descontando su monto dos veces. Ahora DevolucionController::store e IngresoController::anular ejecutan una transacción, adquieren el mismo bloqueo de escritura sobre el ingreso y lo recargan antes de comprobar su estado. El bloqueo usa una escritura sin cambio de monto, compatible también con SQLite. Solo la primera operación válida se completa; la siguiente recibe el mensaje de validación correspondiente. La creación de la devolución/anulación y su auditoría quedan dentro de la transacción. Revisión de sintaxis PHP y de diferencias correcta; tests no ejecutados y validación de concurrencia real pendiente del usuario. No requiere migración.

2. **Aclaración documental resuelta con confirmación explícita del usuario — RFS-64.** Se actualizó RFS-64 para distinguir el pago del arriendo como ingreso y la garantía como registro separado, conforme a RFS-62/63. Se alineó RFU-40 con la misma regla. RFS-62 y RFS-63 se conservaron. No cambió la lógica del sistema. Esta autorización es puntual: cualquier cambio futuro de REQUIREMENTS.md requiere una nueva confirmación explícita del usuario, como se registra en AGENTS.md.

3. **Evidencia de validación por completar.** Hay archivos de grupos, 2a, 2b y 3b; solo se incorpora aquí como ejecución expresamente confirmada la de 3b. Los tests de 3b verifican envíos repetidos de forma secuencial, sin concurrencia real. No hay tests dedicados a generación mensual, edición de horarios, cancelación de ocurrencias recurrentes o impresión. La impresión sí tiene comprobación manual confirmada. Estas ausencias de cobertura no equivalen por sí solas a funcionalidad faltante.

Observación transversal ya conocida, fuera del alcance funcional añadido en sprint 2: la matriz general permite al presidente todos los módulos, pero usuarios/roles continúa restringido al administrador. Mantener pendiente su aclaración sin modificar permisos silenciosamente.

Actas, proyectos, inventario y reportes por período/organización (RFS-69 a 80) corresponden al sprint 3 y no se cuentan como faltantes del sprint 2. No se inició trabajo de ese sprint.
