# Requisitos del sistema

## Perfiles

| Perfil | Definición |
| --- | --- |
| Vecino | Persona que reside en la unidad vecinal. Es un rol de usuario. |
| Socio | Vecino con ficha en estado activo. Se deduce de la ficha; no es un rol aparte. |
| Representante | Persona designada como representante vigente de un grupo. Puede ser socio, dirigente o una persona sin ficha ni cuenta de usuario; no es un rol aparte. |
| Dirigente | Presidente, secretario o tesorero. |
| Administrador | Gestiona usuarios y roles. No es dirigente. |

## Permisos por rol

| Rol | Acceso |
| --- | --- |
| Presidente | Módulos de gestión vecinal, finanzas, actas, proyectos, reportes y auditoría. Sin acceso a la administración de usuarios, roles, parámetros ni catálogos, reservada al administrador. |
| Secretario | Socios, citas, atención al público, certificados, grupos, calendario, reservas, inventario, gestión de solicitudes y préstamos de insumos, y actas. |
| Tesorero | Finanzas, actas y reportes financieros. Puede solicitar insumos para sí, como dirigente, sin obtener permisos de gestión del inventario o de las solicitudes ajenas. |
| Administrador | Usuarios, roles y parámetros, incluidos los catálogos de actividades gratuitas y de insumos. Ve los datos de cuenta de cada usuario (nombre, correo y rol). No ve fichas de socios, finanzas ni actas de directiva; únicamente puede dar de baja la ficha de un dirigente desde Usuarios, con motivo y fecha. |
| Vecino y socio | Registro y solicitud de inscripción. Certificados, calendario, actas de asamblea, solicitudes de sede e insumos según su estado y los permisos específicos. |

Todo usuario con sesión iniciada ve los proyectos en modo solo lectura. En los requisitos, "el presidente o el secretario" indica que basta con uno de los dos.

Los socios activos, incluidos los dirigentes con ficha activa, pueden solicitar insumos en línea. Los dirigentes sin ficha activa conservan los permisos operativos de su cargo, pero no pueden iniciar solicitudes personales de insumos; solicitar insumos no concede acceso a gestionar el inventario. Cualquier vecino sin ficha ni cuenta puede solicitar insumos en ventanilla con nombre, RUT, domicilio y teléfono; si representa una agrupación seleccionada, esta se identifica. La etiqueta de representante en una ficha existente es únicamente informativa y no concede roles ni permisos.

## Requisitos funcionales de usuario (RFU)

Los RFU describen qué puede hacer cada actor, agrupados por módulo.

### 1. Registro e inscripción de socios

- **RFU-01** El vecino puede registrarse en el sistema.
- **RFU-02** El vecino puede solicitar su inscripción, indicando su RUT, declarando al menos 6 meses de residencia y adjuntando carnet de identidad, boleta de servicio básico y teléfono.
- **RFU-03** El vecino puede ver el estado de su solicitud y el motivo de rechazo, si lo hay.
- **RFU-04** El vecino puede corregir y reenviar una solicitud reabierta.
- **RFU-05** El presidente o el secretario puede validar los requisitos de una solicitud y aprobarla o rechazarla indicando un motivo.
- **RFU-06** El presidente o el secretario puede reabrir una solicitud rechazada para que el vecino la corrija.
- **RFU-07** El presidente o el secretario puede liberar los horarios disponibles para citas de firma.
- **RFU-08** El vecino con solicitud aprobada puede agendar una cita para firmar el libro de socios.
- **RFU-09** El vecino puede cancelar su cita antes de que ocurra.
- **RFU-10** El presidente o el secretario puede liberar una hora reservada y eliminar un horario que nadie haya reservado.
- **RFU-11** El presidente o el secretario puede validar al vecino como socio tras la firma, registrando la fecha de ingreso.
- **RFU-12** El presidente o el secretario puede registrar socios presencialmente (ventanilla), con estado inicial "por firmar" o "activo" (este último con su fecha de ingreso).
- **RFU-13** El presidente o el secretario puede ver el carnet y la boleta de servicio básico adjuntos de un socio.
- **RFU-14** El presidente o el secretario puede buscar socios y editar sus datos.
- **RFU-15** El presidente o el secretario puede dar de baja a un socio no dirigente (con motivo y fecha) y reactivarlo. Solo el administrador puede dar de baja la ficha de un dirigente, conservando los permisos operativos de su cargo.
- **RFU-16** El presidente o el secretario puede registrar el correo y la contraseña de un socio registrado en ventanilla para generar su cuenta de usuario.

### 2. Certificados

- **RFU-17** El socio puede solicitar en línea un certificado de residencia, indicando el destinatario y un aporte voluntario, que puede ser $0. Solo un aporte positivo requiere respaldo de transferencia.
- **RFU-18** El socio puede descargar su certificado de residencia desde su perfil durante los 5 días siguientes a su emisión. Vencido el plazo, el enlace desaparece.
- **RFU-19** El socio que no descargó el certificado dentro del plazo puede retirarlo presencialmente en la sede. Si el sistema registra una descarga, debe iniciar una nueva solicitud para obtener otro; el aporte seguirá siendo voluntario.
- **RFU-20** [Eliminado]
- **RFU-21** El vecino no socio puede solicitar el certificado de residencia presencialmente en la sede, acreditando al menos 6 meses de residencia. Podrá aportar voluntariamente en efectivo o por transferencia, o continuar con aporte $0.
- **RFU-22** El presidente o el secretario puede revisar el carnet, la boleta de servicio básico y los 6 meses de residencia del vecino no socio, y dejar constancia de la verificación.
- **RFU-23** El presidente o el secretario puede comprobar el aporte cuando sea positivo y emitir el certificado de residencia, también con aporte $0.
- **RFU-24** El presidente o el secretario puede rechazar una solicitud de certificado indicando un motivo.
- **RFU-25** El presidente o el secretario puede anular un certificado emitido indicando un motivo.
- **RFU-26** El presidente o el secretario puede registrar la fecha en que el vecino retira el certificado.
- **RFU-27** El presidente o el secretario puede ver si el socio descargó el certificado.
- **RFU-28** Cualquier persona puede validar un certificado en línea con su código de verificación, sin cuenta.

### 3. Grupos y organizaciones

- **RFU-29** El presidente o el secretario puede crear y editar grupos, con su nombre y horarios de uso de la sede.
- **RFU-30** El presidente o el secretario puede registrar y editar el representante vigente de un grupo, autocompletando desde socios o dirigentes o ingresando sus datos manualmente, sin exigir ficha de socio ni cuenta de usuario.

### 4. Calendario y reservas de sede

- **RFU-31** El socio puede ver los horarios de la sede como libres u ocupados.
- **RFU-32** El presidente o el secretario puede ver el detalle de cada reserva.
- **RFU-33** El presidente o el secretario puede gestionar en Reservas el uso de la sede por grupos, particulares y actividades de la junta, con o sin aporte. Puede registrar en ventanilla una reserva de una persona de la unidad vecinal sin exigir ficha ni cuenta. El calendario se utiliza para consultar la ocupación.
- **RFU-34** El presidente o el secretario puede crear y editar los horarios fijos de un grupo y generar sus reservas mes a mes.
- **RFU-35** El socio activo puede solicitar el uso de la sede en línea y adjuntar el comprobante cuando corresponda al aporte informado por el presidente o el secretario.
- **RFU-36** El presidente o el secretario puede definir el aporte voluntario y una garantía opcional por reserva, admitiendo $0. Confirmará la recepción de los importes positivos con un comprobante del total, sin registrar todavía el ingreso contable.
- **RFU-37** El presidente o el secretario puede cancelar una reserva, incluida una solicitud de arriendo pendiente.
- **RFU-38** El presidente o el secretario puede imprimir el horario semanal o mensual.
- **RFU-39** Solo el administrador puede configurar los parámetros del sistema: horario de la sede, duración de los bloques, vigencia del certificado, plazo de descarga y plazo de confirmación de solicitudes de sede. También puede gestionar los catálogos de actividades gratuitas y de insumos. El aporte por reserva y el monto y garantía por préstamo se definen por operación, no mediante tarifas fijas de parámetros.

### 5. Ingresos y egresos

- **RFU-40** El presidente o el tesorero puede registrar ingresos con su concepto, monto, medio de pago y comprobante, incluidos los aportes por reservas de sede y los cobros por préstamos de insumos. Las garantías de insumos se registran por separado y solo el monto retenido por daños se convierte en ingreso. Las operaciones gratuitas no generan un ingreso de $0.
- **RFU-41** El presidente o el tesorero puede clasificar las cuotas de socios y los aportes de grupos como ordinarios o extraordinarios. Los aportes por reservas de sede y los cobros por préstamos se identifican mediante su propio concepto financiero.
- **RFU-42** El presidente o el tesorero puede registrar la garantía de un préstamo de insumos y cambiar su estado (recibida, devuelta, por devolver, retenida por daños). Las garantías de sede conservan su gestión hasta resolverse.
- **RFU-43** El presidente o el tesorero puede ingresar manualmente el monto retenido por daños.
- **RFU-44** El presidente o el tesorero puede registrar egresos (servicios básicos y mantención) con su comprobante.
- **RFU-45** El presidente o el tesorero puede devolver una cuota indicando un motivo.
- **RFU-46** El presidente o el tesorero puede anular un movimiento indicando un motivo.
- **RFU-47** El presidente o el tesorero puede consultar el saldo de caja.

### 6. Inventario

- **RFU-48** El presidente o el secretario puede registrar existencias seleccionando insumos del catálogo administrado en Parámetros y editar su cantidad total, sin dejarla por debajo de las unidades comprometidas o fuera de servicio.
- **RFU-49** El presidente o el secretario puede gestionar solicitudes de insumos y registrar en ventanilla las de cualquier vecino sin exigir ficha ni cuenta, con nombre, RUT validado, domicilio y teléfono. Si se selecciona a un representante, se identificará su agrupación. El responsable se asignará automáticamente conforme a RFS-107; definirá aporte total y garantía por préstamo, admitiendo aporte $0.
- **RFU-50** El presidente o el secretario puede registrar la entrega y la devolución completa de un préstamo, con estado de salida/regreso y unidades dañadas. Puede consultar los préstamos atrasados según su fecha comprometida de devolución.

### 7. Actas

- **RFU-51** El presidente o el secretario puede registrar actas de asamblea y de directiva, con un escaneo opcional.
- **RFU-52** El socio activo puede consultar las actas de asamblea.
- **RFU-53** El presidente, el secretario o el tesorero puede consultar las actas de asamblea y de directiva.

### 8. Proyectos

- **RFU-54** El presidente puede registrar y editar los proyectos postulados por la junta.
- **RFU-55** Cualquier usuario con sesión iniciada puede consultar los proyectos y su estado, en modo solo lectura y por transparencia.

### 9. Reportes

- **RFU-56** El presidente o el tesorero puede generar reportes financieros (pagos recibidos, egresos, arriendos por periodo y por organización).
- **RFU-57** El presidente o el secretario puede obtener el listado de socios en Excel o PDF.

### 10. Auditoría

- **RFU-58** El presidente puede consultar quién modificó los datos de un socio y cuándo.
- **RFU-59** El presidente puede consultar la trazabilidad de los certificados (emisión, descarga, retiro y anulación).
- **RFU-60** El presidente puede consultar la trazabilidad de las finanzas (ingresos, egresos, anulaciones, devoluciones y garantías), filtrando por fecha, autor y tipo de movimiento.
- **RFU-61** El presidente puede consultar los cambios en las cuentas de usuario (altas, cambios de rol y de correo).

### 11. Administración y cuenta de usuario

- **RFU-62** El administrador puede gestionar usuarios y ver los datos de cuenta de cada uno (nombre, correo y rol).
- **RFU-63** El administrador puede gestionar roles.
- **RFU-64** El vecino puede eliminar su cuenta desde su perfil. Los dirigentes no podrán eliminar su propia cuenta; solo un administrador podrá eliminarla, conservando la ficha y los registros históricos. Ningún administrador podrá eliminar su propia cuenta.

- **RFU-65** El socio activo puede emitir autónomamente su certificado de socio. El presidente o el secretario puede emitirlo en ventanilla exclusivamente para imprimir, sin incorporarlo a las descargas del socio.
- **RFU-66** El presidente o el secretario puede gestionar horarios semanales de atención al público, con varios tramos por día, visibles en la portada pública e independientes de las citas de firma.
- **RFU-67** El socio activo, incluido el dirigente con ficha activa, puede solicitar insumos en línea, indicando los insumos y cantidades y su agrupación cuando sea representante. El sistema le recuerda los horarios públicos de atención para retirar los insumos.

## Requisitos funcionales de sistema (RFS)

Los RFS traducen los RFU en comportamiento verificable del sistema y fijan las reglas de negocio acordadas.

### 1. Registro, inscripción y socios

- **RFS-01** El sistema deberá permitir el registro de vecinos con nombre, correo y contraseña, asignándoles el rol vecino.
- **RFS-02** El sistema deberá exigir el RUT en la solicitud de inscripción, validar su dígito verificador y rechazar un RUT que ya figure como socio. En ese caso, el vecino debe acercarse a la sede.
- **RFS-03** El sistema deberá recibir una solicitud de inscripción con RUT, declaración de residencia (al menos 6 meses), carnet, boleta de servicio básico y teléfono, y no permitir enviarla si falta alguno.
- **RFS-04** El sistema deberá manejar los estados de la ficha del socio: pendiente, por firmar (solicitud aprobada), activo, rechazado e inactivo.
- **RFS-05** El sistema deberá exigir un motivo obligatorio al rechazar una solicitud y mostrarlo al vecino.
- **RFS-06** El sistema deberá permitir al presidente o al secretario reabrir una solicitud rechazada, devolviéndola al estado "pendiente", y habilitar al vecino para corregirla y reenviarla.
- **RFS-07** El sistema deberá permitir al presidente o al secretario definir bloques de citas disponibles (de 15, 20, 30, 45 o 60 minutos, con hasta 6 meses de anticipación) y mostrarlos solo a vecinos con solicitud en estado "por firmar".
- **RFS-08** El sistema deberá permitir al vecino con solicitud aprobada reservar un bloque de cita y bloquearlo para otros, con una sola cita por vecino.
- **RFS-09** El sistema deberá permitir al vecino cancelar su cita futura, dejando el bloque libre.
- **RFS-10** El sistema deberá permitir al presidente o al secretario liberar una hora reservada y eliminar un bloque que nadie haya reservado.
- **RFS-11** El sistema deberá permitir al presidente o al secretario validar al socio tras la firma, cambiando su ficha a estado "activo" y registrando la fecha de ingreso.
- **RFS-12** El sistema deberá permitir registrar socios en ventanilla sin cuenta de usuario, con estado inicial "por firmar" o "activo" (este con fecha de ingreso), correo y adjuntos opcionales, validando el RUT y los 6 meses de residencia.
- **RFS-13** El sistema deberá permitir al presidente o al secretario ver los documentos adjuntos (carnet y boleta) de un socio.
- **RFS-14** El sistema deberá permitir al presidente o al secretario buscar socios por nombre o RUT, filtrarlos por estado y editar sus datos. Al editar, la regla de 6 meses de residencia solo se exige si cambia la fecha de residencia.
- **RFS-15** El sistema deberá permitir al presidente o al secretario asignar correo y contraseña a un socio registrado en ventanilla, generando su cuenta con rol vecino y vinculándola a su ficha.
- **RFS-16** El sistema deberá permitir dar de baja a un socio no dirigente con motivo y fecha obligatorios, y reactivarlo. Solo el administrador podrá dar de baja la ficha de socio de un dirigente desde Usuarios. Presidente y secretario no podrán dar de baja fichas de dirigentes, incluida la propia.
- **RFS-17** El sistema deberá retirar de inmediato a un socio dado de baja los accesos propios de socio, como las actas de asamblea y las solicitudes en línea de certificado, sede e insumos. Se conservan los permisos operativos que le correspondan por su rol; un dirigente también necesitará ficha activa para solicitar insumos para sí.
- **RFS-18** El sistema deberá registrar en cada cambio sobre socios su autor, fecha y el dato modificado.

### 2. Certificados

- **RFS-19** El sistema deberá permitir a un socio activo solicitar un certificado de residencia en línea, con destinatario y aporte voluntario mayor o igual que $0. El valor configurable de Parámetros será únicamente una sugerencia. Guardará el importe elegido en la solicitud; si es positivo exigirá comprobante y número de transferencia; con $0 no exigirá respaldo ni creará ingreso.
- **RFS-20** El sistema deberá limitar a 3 las solicitudes de certificado pendientes por usuario.
- **RFS-21** El sistema deberá permitir al presidente o al secretario emitir el certificado tras revisar los documentos y verificar el aporte solo cuando sea positivo. La ausencia de aporte no impedirá emitirlo.
- **RFS-22** El sistema deberá permitir al presidente o al secretario rechazar una solicitud de certificado con motivo obligatorio.
- **RFS-23** El sistema deberá permitir al presidente o al secretario anular un certificado emitido con motivo obligatorio.
- **RFS-24** El sistema deberá habilitar la descarga del certificado en el perfil del socio durante 5 días desde su emisión (plazo configurable) y retirar el enlace al vencer ese plazo.
- **RFS-25** El sistema deberá registrar fecha y hora de cada descarga de certificado de residencia y mostrarlas al presidente o al secretario. Las descargas del certificado de socio no se incorporarán a auditoría ni a este registro.
- **RFS-26** El sistema deberá permitir retirar presencialmente un certificado emitido que el socio no descargó dentro del plazo, sin extender su vigencia. Si existe una descarga registrada, deberá exigir una nueva solicitud, manteniendo el carácter voluntario del aporte.
- **RFS-27** El sistema deberá permitir al presidente o al secretario emitir un certificado de residencia a un vecino no socio, tras verificar su carnet, boleta de servicio básico y 6 meses de residencia, registrando sus datos, destinatario y aporte voluntario, incluido $0. Solo un aporte positivo requiere verificar su recepción y registrar talonario o número de transacción.
- **RFS-28** El sistema deberá asignar a cada certificado un folio único y correlativo por año (formato AAAA-NNNN) al emitirlo, sin consumir folios en las solicitudes rechazadas.
- **RFS-29** El sistema deberá asignar a cada certificado un código de verificación aleatorio, distinto del folio, para su validación pública.
- **RFS-30** El sistema deberá guardar en cada certificado los datos del solicitante, las fechas de emisión y caducidad, el folio, el código de verificación, el destinatario y una copia del nombre y cargo del otorgante al emitirlo. Las modificaciones o eliminación posteriores de su cuenta no cambiarán esa copia ni el PDF regenerado. Los certificados históricos sin esa copia se conservarán, indicando la ausencia de identidad histórica registrada sin inventarla.
- **RFS-31** El sistema deberá fijar las fechas de emisión y de caducidad al emitirse el certificado, con una vigencia de 90 días configurable. La descarga o el retiro no modifican la vigencia.
- **RFS-32** El sistema deberá permitir registrar la fecha de retiro del certificado.
- **RFS-33** [Eliminado]
- **RFS-34** [Eliminado]
- **RFS-35** El sistema deberá ofrecer una página pública de validación donde, con el código de verificación, cualquiera pueda comprobar la autenticidad y el estado de un certificado (vigente, vencido o anulado), sin iniciar sesión, mostrando solo los datos necesarios con el RUT enmascarado y limitando la cantidad de consultas por minuto.
- **RFS-36** El sistema deberá generar el certificado en PDF y marcarlo con una marca de agua de borrador mientras no esté emitido.
- **RFS-37** El sistema deberá registrar automáticamente como ingreso con concepto certificados el aporte voluntario positivo guardado en el certificado de residencia, al emitirlo, sin requerir acceso del emisor a finanzas. No tomará un precio nuevo de Parámetros para cambiar una solicitud que ya tiene importe definido; con aporte $0 no generará ingreso.

### 3. Grupos y organizaciones

- **RFS-38** El sistema deberá permitir crear y editar grupos con nombre y horarios de uso de la sede.
- **RFS-39** El sistema deberá asociar a cada grupo un único representante vigente, sin exigir ficha de socio ni cuenta de usuario. Permitirá seleccionar socios o dirigentes para autocompletar los datos o ingresarlos manualmente. El RUT del representante será obligatorio y validado; los registros históricos sin RUT se conservarán y deberán completarlo al volver a operar.
- **RFS-40** El sistema deberá copiar al grupo los datos de contacto del representante (nombre, correo, teléfono, dirección) y permitir editarlos sin alterar la ficha del representante.
- **RFS-41** El sistema deberá conservar al representante del grupo aunque sea dado de baja como socio. Solo se lo retirará o reemplazará mediante una acción explícita en la gestión del grupo.
- **RFS-42** Mientras un grupo no tenga representante, el sistema no deberá generar ni aceptar nuevas reservas ni arriendos a nombre del grupo. Las reservas ya generadas se mantienen.

### 4. Calendario y reservas de sede

- **RFS-43** El sistema deberá mostrar un calendario digital de consulta con la ocupación de la sede. La creación y gestión de reservas estarán separadas del calendario, en el módulo Reservas, que incluirá un apartado de insumos.
- **RFS-44** El sistema deberá registrar en cada reserva día, hora, actividad, quién reserva y quién la ingresa; el motivo se completará desde la actividad. El catálogo inicial será Actividad de la junta, Actividad comunitaria, Actividad de agrupación, Actividad privada y Taller. Solo el administrador podrá ampliar, renombrar y desactivar actividades, conservando las denominaciones históricas de reservas. Las reservas privadas de vecinos sin ficha se registrarán exclusivamente en ventanilla con los antecedentes de RFS-50. Las actividades adicionales del catálogo mantendrán aporte $0.
- **RFS-45** El sistema deberá limitar las reservas al horario de la sede (por defecto de 09:00 a 22:00) en bloques de 1 hora, ambos configurables.
- **RFS-46** El sistema deberá rechazar cualquier reserva que se superponga con un horario ocupado.
- **RFS-47** El sistema deberá modelar el horario fijo de un grupo como reserva recurrente, cuyas reservas genera cada mes el presidente o el secretario y que se confirman automáticamente.
- **RFS-48** El sistema no deberá permitir que un arriendo se superponga con un horario fijo de grupo.
- **RFS-49** El sistema deberá permitir al presidente o al secretario editar los horarios fijos de los grupos.
- **RFS-50** El sistema permitirá solicitar sede en línea a socios activos, incluidos dirigentes con ficha activa, y en ventanilla a cualquier vecino con nombre, RUT validado, domicilio y teléfono, sin exigir ficha ni cuenta. Presidente o secretario definirá el aporte y la garantía opcional de cada reserva. Si el total es positivo se adjuntará un comprobante por ambos importes; si ambos son $0 no se exigirá comprobante. El titular en línea adjuntará su respaldo; la directiva podrá adjuntar el recibido en ventanilla. La garantía se conservará separada del ingreso.
- **RFS-51** Las solicitudes de sede con aporte o garantía quedarán pendientes, bloqueando el horario hasta su confirmación por presidente o secretario dentro de RFS-52. Se conservará el registro directo de actividades sin aporte ni garantía y las reservas recurrentes de grupos.
- **RFS-52** El sistema deberá liberar automáticamente el horario de una solicitud de sede pendiente si no se confirma dentro del plazo configurable (por defecto 48 horas desde la solicitud), sin perjuicio de la cancelación manual.
- **RFS-53** Presidente o secretario confirmará la reserva verificando los importes positivos: aporte y garantía, con un comprobante del total recibido. Si ambos son $0 podrá confirmarse sin comprobante. Esta confirmación no crea el ingreso financiero; el registro posterior en Finanzas no condiciona la ocupación confirmada.
- **RFS-54** El sistema permitirá cancelar una reserva y liberar el horario. Si existe garantía recibida, nueva o histórica, quedará por devolver, conservando su registro y resolución financiera.
- **RFS-55** El sistema deberá mostrar a los socios los horarios como libres u ocupados, sin detalle.
- **RFS-56** El sistema deberá mostrar al presidente y al secretario el detalle de cada reserva.
- **RFS-57** El sistema deberá permitir al presidente o al secretario imprimir el horario semanal y mensual.

### 5. Ingresos y egresos

- **RFS-58** El sistema deberá registrar cada ingreso con concepto (aporte voluntario por reserva de sede, préstamo de insumos, cuota de socios, aporte de grupos, certificados, otros), fecha de pago, monto, medio de pago (efectivo o transferencia), quién lo ingresa y comprobante. Los conceptos anteriores se conservarán en los movimientos históricos, sin reclasificarlos automáticamente.
- **RFS-59** El sistema deberá exigir número de talonario para aportes en efectivo y, para transferencias, archivo de comprobante o número de transacción. Los ingresos manuales ofrecerán adjuntar PDF, JPG o PNG de hasta 5 MB en almacenamiento privado, consultable solo por presidente o tesorero. Se conservará el archivo al anular el ingreso.
- **RFS-60** El sistema deberá exigir clasificar las cuotas de socios y los aportes de grupos como ordinarios o extraordinarios, sin asociarlos a proyectos. Los aportes por reservas de sede y cobros por préstamos se registrarán con sus conceptos propios, sin esa clasificación.
- **RFS-61** Presidente o secretario definirá por operación el aporte voluntario de sede y una garantía opcional, sin tarifas fijas. Ambos podrán ser $0. La garantía no se sumará a los ingresos y se resolverá conforme a RFS-62/63. Se conservarán importes y condiciones históricos.
- **RFS-62** El sistema deberá gestionar la garantía de insumos por préstamo, separada de los ingresos, con estados: recibida, devuelta, por devolver y retenida por daños. Las garantías de sede conservarán sus montos y estados y podrán resolverse con las reglas existentes. Estas reglas se aplican tanto a sede como a insumos, con garantías nuevas o históricas.
- **RFS-63** El sistema deberá convertir la garantía en ingreso solo cuando su estado sea "retenida por daños", usando el monto ingresado manualmente, sin superar la garantía recibida. Se permitirá retención parcial y devolución del resto en una misma operación, guardando ambos montos, fecha, motivo y autor. Las garantías nuevas de sede siguen el mismo criterio.
- **RFS-64** El sistema deberá permitir al presidente o al tesorero registrar como ingresos los aportes recibidos por reservas de sede y los cobros recibidos por préstamos de insumos, vinculados a su operación. Las garantías de sede e insumos se registrarán por separado, conforme a RFS-62 y RFS-63. Las operaciones de monto $0 no crearán ingresos; se conservará la trazabilidad de las operaciones y garantías históricas de sede.
- **RFS-65** El sistema deberá registrar egresos (servicios básicos y mantención) con fecha, número de transacción, copia del comprobante y quién pagó.
- **RFS-66** El sistema deberá permitir devolver una cuota con motivo obligatorio y descontar el monto de la caja.
- **RFS-67** El sistema no deberá permitir borrar movimientos; solo anularlos con motivo obligatorio.
- **RFS-68** El sistema deberá calcular y mostrar el saldo de caja a partir de ingresos, egresos y devoluciones.

### 6. Inventario

- **RFS-69** El sistema deberá registrar existencias seleccionando un insumo del catálogo administrado en Parámetros y su cantidad total. Presidente o secretario podrá editar esa cantidad respetando RFS-101. El catálogo incluirá los insumos predefinidos de RFS-95.
- **RFS-70** El sistema deberá registrar solicitudes y préstamos con uno o varios insumos, cada uno con su cantidad y estado de salida/regreso, fecha de solicitud, fecha de entrega, fecha comprometida y fecha efectiva de devolución, solicitante y sus datos de contacto, agrupación cuando corresponda y responsable asignado automáticamente conforme a RFS-107, exclusivamente presidente o secretario. Se distinguirá del solicitante y se conservarán su nombre y cargo históricos.
- **RFS-71** El sistema deberá permitir solicitudes de insumos en línea a socios activos, incluidos dirigentes con ficha activa. En ventanilla, presidente o secretario podrá registrar a cualquier vecino sin ficha ni cuenta, conservando nombre, RUT validado, domicilio y teléfono. Los representantes seleccionados deberán identificar su agrupación; el registro manual de un vecino particular no exigirá agrupación. La solicitud podrá incluir varios insumos y sus cantidades.
- **RFS-72** El sistema deberá impedir aprobar o entregar más unidades de las disponibles, comprobando las existencias en el servidor y evitando sobreasignaciones simultáneas. Las solicitudes pendientes no descontarán unidades; las aprobadas reservarán las cantidades de todos sus insumos. Disponibles será el total menos las unidades reservadas pendientes de entrega, las entregadas aún no devueltas y las fuera de servicio, sin contar dos veces las mismas unidades.

### 7. Actas

- **RFS-73** El sistema deberá registrar actas con fecha, tipo (asamblea o directiva), acuerdos en texto y escaneo opcional.
- **RFS-74** El sistema deberá mostrar las actas de asamblea solo a socios activos y dirigentes.
- **RFS-75** El sistema deberá mostrar las actas de directiva solo a dirigentes (presidente, secretario y tesorero).

### 8. Proyectos

- **RFS-76** El sistema deberá permitir al presidente registrar y editar proyectos con título, organismo, fecha de postulación, estado (postulado, adjudicado o rechazado) y monto opcional.
- **RFS-77** El sistema deberá seleccionar de los dirigentes (presidente, secretario o tesorero) a quien ingresó el proyecto en la plataforma del organismo, al registrar un proyecto o cambiar a esa persona en uno existente, sin admitir un nombre nuevo como texto libre. Completará automáticamente quién lo registró en el sistema y mantendrá la distinción entre ambas personas. Los nombres guardados en proyectos anteriores se conservarán al consultar o editar otros datos, sin reasignarlos automáticamente.
- **RFS-78** El sistema deberá mostrar los proyectos en modo solo lectura a todo usuario con sesión iniciada, sin posibilidad de postular.

### 9. Reportes

- **RFS-79** El sistema deberá generar reportes de pagos recibidos, egresos y arriendos por periodo y por organización, disponibles para el tesorero y el presidente.
- **RFS-80** El sistema deberá generar el listado de socios en Excel y PDF, disponible para el secretario y el presidente.

### 10. Roles, permisos, auditoría, parámetros y cuenta

- **RFS-81** El sistema deberá restringir el acceso según el rol: presidente a los módulos de gestión vecinal, finanzas, actas, proyectos, reportes y auditoría, sin acceso a la administración de usuarios, roles, parámetros ni catálogos; secretario a socios, citas, atención al público, certificados, grupos, calendario, reservas, gestión de inventario y préstamos, y actas; tesorero a finanzas, actas y reportes financieros. La administración de usuarios, roles, parámetros y catálogos será exclusiva del administrador. Todos los dirigentes podrán solicitar insumos para sí, sin que ese permiso conceda al tesorero gestión de inventario, aprobación de solicitudes o acceso a préstamos ajenos. Excepción administrativa: el administrador podrá dar de baja la ficha de un dirigente desde Usuarios, sin acceso general a la gestión vecinal.
- **RFS-82** El sistema deberá autorizar las acciones propias de un socio (certificado de residencia en línea, actas de asamblea y solicitudes de sede e insumos) según el estado activo de su ficha y no según su rol. Los dirigentes también deberán tener ficha activa para sus trámites personales, incluidos certificados de residencia y solicitudes de sede e insumos; conservarán los permisos operativos de su cargo durante la regularización.
- **RFS-83** El sistema deberá permitir exclusivamente al administrador gestionar usuarios, roles y parámetros, y ver el nombre, correo y rol de cada usuario, sin acceso a fichas de socios, finanzas ni actas de directiva. Se permite únicamente la operación de baja de ficha de dirigente descrita en RFS-16, con motivo y fecha.
- **RFS-84** El sistema no deberá permitir que un administrador cambie su propio rol.
- **RFS-85** El sistema deberá registrar fecha, autor, cambios y dirección IP en socios, certificados, usuarios, finanzas y cuotas devueltas. Auditará las descargas de certificados de residencia, excluyendo las descargas del certificado de socio.
- **RFS-86** El sistema no deberá permitir editar ni eliminar los registros de trazabilidad.
- **RFS-87** El sistema deberá permitir solo al presidente consultar la auditoría, con filtros por fecha, autor y tipo de registro (socio, certificado, usuario o finanzas).
- **RFS-88** El sistema deberá permitir exclusivamente al administrador configurar el horario de la sede, la duración de los bloques, la vigencia del certificado, el plazo de descarga, el plazo de confirmación de solicitudes de sede y los catálogos de actividades gratuitas e insumos. No se definirán tarifas fijas de sede ni precios por insumo en Parámetros: presidente o secretario definirá el aporte de cada reserva y el monto y garantía de cada préstamo, además de la garantía opcional de sede.
- **RFS-89** El sistema deberá permitir al vecino eliminar su cuenta desde su perfil y al administrador eliminar otras cuentas desde Usuarios, conservando la ficha y los registros históricos sin vínculo al usuario. Los dirigentes no podrán eliminar su cuenta propia. Ningún administrador podrá eliminar su propia cuenta.

- **RFS-90** El sistema deberá emitir inmediatamente el certificado de socio solicitado desde una cuenta con ficha activa, sin revisión de presidente o secretario, usando al presidente vigente como otorgante. Si lo emite presidente o secretario en ventanilla, quedará disponible solo para impresión y no se incorporará a las descargas del socio. El certificado de residencia mantendrá revisión de documentos y verificación del aporte únicamente si es positivo; admite aporte $0.
- **RFS-91** El sistema deberá permitir a presidente y secretario establecer horarios semanales de atención al público con varios tramos por día y mostrarlos en la portada pública. Las citas de firma serán independientes y podrán generarse en cualquier horario permitido para citas; al aprobar la documentación de un vecino, se mostrará un recordatorio para liberar citas, sin crearlas automáticamente ni restringirlas al horario de atención.

### 11. Navegación, reservas e insumos — ampliación acordada

- **RFS-92** El menú lateral mantendrá Reservas con Reservas de sede y Solicitudes de insumos. Inventario será un desplegable separado con Registrar insumo (registro y edición de existencias) e Historial de préstamos (consulta). Las operaciones de préstamos continuarán en Reservas. Finanzas incluirá Ingresos por aprobar, Ingresos, Egresos y consultas y gestión de garantías. Se mantienen Horarios de atención, los trámites personales de dirigentes en el menú de cuenta y Auditoría al final.
- **RFS-93** El sistema deberá situar el control para mostrar u ocultar la contraseña al lado del campo correspondiente en el formulario del administrador para editar usuarios.
- **RFS-94** El sistema deberá mostrar una etiqueta informativa de representante en la ficha existente que corresponda a una persona designada en un grupo. La etiqueta no será un rol ni concederá permisos o habilitará funciones. No se exigirá crear ficha o cuenta a un representante externo para mostrar o gestionar su representación en el grupo.
- **RFS-95** El sistema deberá ofrecer un catálogo de insumos organizado en categorías, ampliable desde Parámetros exclusivamente por el administrador. Incluirá: mobiliario y eventos (sillas, mesas, toldos, carpas estructurales, vajilla y mantelería); equipamiento (equipos de amplificación); herramientas (de jardinería, eléctricas, escalas y escaleras extensibles); y ayudas técnicas en comodato (catres clínicos, sillas de ruedas, burritos, muletas y bastones). La selección de insumos se realizará mediante listas desplegables; el catálogo no fijará precios ni garantías por unidad.
- **RFS-96** El sistema deberá distinguir los estados pendiente, aprobado con unidades reservadas, entregado y devuelto en las solicitudes y préstamos de insumos. Presidente o secretario comprobará y reservará las existencias de todos los insumos al aprobar, y registrará posteriormente la entrega. El solicitante no podrá aprobar su propia solicitud mediante el permiso de consulta o solicitud; la gestión requerirá el permiso de presidente o secretario. La reserva y la entrega no descontarán dos veces las mismas unidades.
- **RFS-97** El sistema deberá permitir al presidente o al secretario que gestione el préstamo definir manualmente un monto total y una garantía por préstamo. El monto podrá ser $0. Ambos valores corresponderán al conjunto de insumos de esa operación, sin multiplicarse por unidades, tipos de insumo o días. Se guardarán en el préstamo, separados del catálogo, y la garantía se gestionará conforme a RFS-62/63, independientemente del ingreso por el cobro.
- **RFS-98** El sistema deberá recordar al solicitante los horarios públicos de atención de la directiva para retirar insumos, sin crear citas de firma. Un préstamo entregado, aún no devuelto y con fecha comprometida anterior al día actual se mostrará atrasado. Los atrasos se indicarán mediante un punto rojo, sin contadores, en el acceso correspondiente del menú para presidente y secretario; las solicitudes de insumos pendientes se señalarán del mismo modo en su bandeja. Estos avisos no requerirán correo ni un estado de lectura.
- **RFS-99** El sistema deberá registrar la devolución completa de todos los insumos de un préstamo, sin devoluciones parciales ni devoluciones duplicadas. Guardará fecha efectiva, estado de regreso, unidades dañadas por insumo y autor. Las unidades utilizables volverán a estar disponibles y las dañadas permanecerán fuera de servicio. Una cantidad dañada no podrá superar las unidades prestadas del insumo. No se incorpora un flujo de reparación o reincorporación en esta entrega.
- **RFS-100** El sistema deberá conservar las reservas, pagos, garantías, préstamos y datos de proyectos anteriores a esta ampliación. No eliminará ni recalculará sus montos, autores o condiciones para adecuarlos a las reglas nuevas. Las garantías de sede conservarán su resolución financiera; los registros anteriores que no cumplan las nuevas condiciones de elegibilidad o catálogo mantendrán su historial, sin habilitar nuevas operaciones contrarias a las reglas vigentes.
- **RFS-101** El sistema deberá permitir a presidente o secretario editar la cantidad total de existencias de un insumo, sin admitir cantidades negativas ni un total inferior a las unidades reservadas para entrega, prestadas sin devolver y fuera de servicio. La edición de existencias y la aprobación/entrega/devolución de préstamos deberán comprobarse de forma consistente ante operaciones simultáneas, conservando el historial.

### 12. Ajustes de cierre autorizados

- **RFS-102** Los dirigentes deberán disponer de ficha de socio con los mismos antecedentes y validación que los demás socios: nombre, RUT validado, dirección, teléfono, residencia y documentos. El sistema reutilizará la ficha existente. Quien no tenga ficha activa podrá completar o consultar su inscripción y reservar su cita de firma, conservando sus permisos de cargo. Sus trámites personales requerirán ficha activa. Otro presidente o secretario deberá resolver su inscripción; nadie podrá aprobar su propia ficha. No se activarán fichas automáticamente ni se duplicarán registros existentes.
- **RFS-103** Los selectores de fecha deberán permitir elegir directamente mes y año y mostrar la fecha como dd/mm/aaaa, con independencia del idioma del navegador. Los desplegables mantendrán separación entre texto y flecha. La interfaz de acceso utilizará la identidad de JJVV Digital en lugar del logo de Laravel; la portada destacará Verificar un certificado. El listado de proyectos no mostrará el formulario de filtros.

- **RFS-104** Los montos recibidos para certificados, uso de sede, préstamos y aportes de socios o agrupaciones se presentarán como aportes voluntarios destinados a mejorar la comunidad. Las garantías se identificarán separadamente como montos sujetos a devolución; los egresos conservarán su identificación como gastos. Se conservarán las claves y operaciones históricas, sin reclasificar ni recalcular sus importes.


### 13. Correcciones autorizadas de interfaz y permisos — 19/10/2026

- **RFS-106** El selector de actividad de la reserva de sede tendrá inicialmente Actividad de la junta, Actividad comunitaria, Actividad de agrupación, Actividad privada y Taller, conservando la administración del catálogo en Parámetros y las denominaciones históricas de reservas existentes. No incluirá Reserva de sede como actividad. El campo En representación de solo se habilitará al seleccionar Actividad de agrupación y mostrará únicamente nombres de agrupaciones; al cambiar de actividad se limpiará el vínculo. Las solicitudes en línea conservarán el requisito de ficha activa y podrán vincularse a una agrupación que el solicitante represente. Se mantienen las reglas de aportes existentes y las actividades adicionales gratuitas.
- **RFS-105** La reserva no mostrará el texto sobre el destino comunitario y último paso del aporte. Presidente y secretario no verán la sección de horarios de atención para retirar insumos; los solicitantes personales conservarán ese recordatorio.


### 14. Responsable y bandeja financiera autorizados — 19/10/2026

- **RFS-107** El responsable del préstamo será exclusivamente presidente o secretario. En ventanilla será quien registre la solicitud; en una solicitud en línea, quien la apruebe. Se asignará automáticamente sin combobox y se conservarán nombre y cargo históricos, aunque posteriormente cambie la cuenta o el rol. No se reasignarán los préstamos históricos ya aprobados.
- **RFS-108** Finanzas ofrecerá a presidente y tesorero una bandeja de Ingresos por aprobar con reservas confirmadas y préstamos aprobados que tengan importes pendientes. Mostrará identidad, aporte, garantía y respaldo o datos de recepción; permitirá registrar cada operación desde el listado, sin abrir su detalle. La aprobación creará solo el ingreso del aporte positivo y registrará la garantía por separado; si solo hay garantía no creará ingreso de cero. Excluirá operaciones con ambos importes en cero y las ya registradas; permitirá reingresar aportes anulados sin repetir garantías, conservando los bloqueos contra duplicación. Los detalles de reservas y solicitudes no contendrán formularios de registro financiero. La resolución de garantías seguirá disponible en Finanzas.
