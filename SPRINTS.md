## Plan de sprints

Los REQUISITOS.md fueron organizados según el método de prioridad MosCow para definir los sprints del proyecto.

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

### [COMPLETADO] Sprint 2: SHOULD HAVE

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

Se cerró el sprint 1, y se continúa con el sprint 2. A continuación se presentan hallazgos y planes para avanzar.

### Hallazgos:

- user_id tiene nullOnDelete. Esto podría afectar la trazabilidad, pero no se abordará.
- Trazabilidad financiera: el filtro “finanzas” de la auditoría hoy apunta solo a ingresos. Con egresos y devoluciones necesita aceptar varios modelos. Es un cambio pequeño en AuditoriaController y Auditoria::MODELOS. Incluir en bloque 2b.

### Bloque 1 [Completado]: 

grupos y representante (RFS-38 a RFS-42, RFU-29 y RFU-30). Crear y editar grupos con horarios de uso, asignar un representante que sea socio activo con cuenta, y copiar sus datos de contacto al grupo. Cuando un socio se da de baja, su grupo queda sin representante y no acepta reservas nuevas. Esto se conecta con la baja que ya existe en SocioController.

### Bloque 2a [Completado]: ingresos, cuotas y saldo.

- Ampliar los conceptos de ingreso con “cuota de socios” y “aporte de grupos”, además de los actuales “certificados” y “otros” (RFS-58). El concepto “arriendo” llega en el bloque 3b.
- Clasificar cada cuota como ordinaria o extraordinaria, obligatorio solo para cuotas de socios (RFS-60).
- Anular un ingreso con motivo obligatorio, sin borrarlo (RFS-67).
- Saldo de caja: ingresos menos egresos menos devoluciones, sin contar lo anulado (RFS-68).

### Bloque 2b [Completado]: egresos y devoluciones.

- Egresos con fecha, monto, concepto (servicios básicos o mantención), número de transacción, copia del comprobante y quién pagó (RFS-65).
- Devolución de una cuota con motivo obligatorio, que descuenta del saldo de caja (RFS-66).
Anulación de egresos, con el mismo criterio que ingresos.
- Anulación: marcar el registro con fecha, motivo y autor, sin crear un movimiento contrario. 
- Devolución de cuota: guardarla como movimiento propio, ligado a la cuota original, y no como un ingreso con monto negativo.

### Bloque 3a: 

calendario y reservas (RFS-43, RFS-44, RFS-47 a RFS-49). Modelo de reserva con día, hora, motivo, tipo, quién reserva y quién la ingresa. Calendario digital, horarios fijos de grupo que generan reservas cada mes, y bloqueo de superposiciones.

### Bloque 3b: 

arriendos y garantía (RFS-50 a RFS-54, RFS-61 a RFS-64). Solicitud de arriendo por socios activos, estado pendiente que bloquea el horario, liberación automática a las 48 horas, validación de pago y garantía, cancelación, y garantía como registro separado con sus estados. Aquí se usan los parámetros de arriendo que ya existen.

### Bloque 4: 

trazabilidad financiera y horario imprimible (RFU-60, RFS-85). Consulta de finanzas con filtros por fecha, autor y tipo de movimiento, y la vista imprimible del horario.