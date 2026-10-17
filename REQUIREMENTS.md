# Requisitos del sistema

## Perfiles

| Perfil | Definición |
| --- | --- |
| Vecino | Persona que reside en la unidad vecinal. Es un rol de usuario. |
| Socio | Vecino con ficha en estado activo. Se deduce de la ficha; no es un rol aparte. |
| Representante | Socio activo designado como representante vigente de un grupo. Se deduce de la designación; no es un rol aparte. |
| Dirigente | Presidente, secretario o tesorero. |
| Administrador | Gestiona usuarios y roles. No es dirigente. |

## Permisos por rol

| Rol | Acceso |
| --- | --- |
| Presidente | Todos los módulos, incluida la auditoría. |
| Secretario | Socios, citas, certificados, grupos, calendario, inventario y actas. |
| Tesorero | Finanzas, actas y reportes financieros. |
| Administrador | Usuarios y roles. Ve los datos de cuenta de cada usuario (nombre, correo y rol). No ve fichas de socios, finanzas ni actas de directiva. |
| Vecino y socio | Registro, solicitud de inscripción, certificados, calendario y actas de asamblea, según su estado. |

Todo usuario con sesión iniciada ve los proyectos en modo solo lectura. En los requisitos, "el presidente o el secretario" indica que basta con uno de los dos.

## Requisitos funcionales de usuario (RFU)

Los 64 RFU describen qué puede hacer cada actor, agrupados en 11 módulos.

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
- **RFU-15** El presidente o el secretario puede dar de baja a un socio (con motivo y fecha) y reactivarlo.
- **RFU-16** El presidente o el secretario puede registrar el correo y la contraseña de un socio registrado en ventanilla para generar su cuenta de usuario.

### 2. Certificados

- **RFU-17** El socio puede solicitar en línea un certificado de residencia, indicando el destinatario y adjuntando el comprobante de transferencia de $1.000.
- **RFU-18** El socio puede descargar su certificado de residencia desde su perfil durante los 5 días siguientes a su emisión. Vencido el plazo, el enlace desaparece.
- **RFU-19** El socio que no descargó el certificado dentro del plazo puede retirarlo presencialmente en la sede. Si el sistema registra una descarga, debe pagar de nuevo para obtener otro.
- **RFU-20** [Eliminado]
- **RFU-21** El vecino no socio puede solicitar el certificado de residencia presencialmente en la sede, acreditando al menos 6 meses de residencia y pagando $1.000 en efectivo o por transferencia.
- **RFU-22** El presidente o el secretario puede revisar el carnet, la boleta de servicio básico y los 6 meses de residencia del vecino no socio, y dejar constancia de la verificación.
- **RFU-23** El presidente o el secretario puede comprobar el pago y emitir el certificado de residencia.
- **RFU-24** El presidente o el secretario puede rechazar una solicitud de certificado indicando un motivo.
- **RFU-25** El presidente o el secretario puede anular un certificado emitido indicando un motivo.
- **RFU-26** El presidente o el secretario puede registrar la fecha en que el vecino retira el certificado.
- **RFU-27** El presidente o el secretario puede ver si el socio descargó el certificado.
- **RFU-28** Cualquier persona puede validar un certificado en línea con su código de verificación, sin cuenta.

### 3. Grupos y organizaciones

- **RFU-29** El presidente o el secretario puede crear y editar grupos, con su nombre y horarios de uso de la sede.
- **RFU-30** El presidente o el secretario puede asignar el representante vigente de un grupo (un socio activo con cuenta de usuario) y editar sus datos de contacto.

### 4. Calendario de espacios

- **RFU-31** El socio puede ver los horarios de la sede como libres u ocupados.
- **RFU-32** El presidente o el secretario puede ver el detalle de cada reserva.
- **RFU-33** El presidente o el secretario puede ingresar reservas de uso de grupo, arriendo y actividad de la junta.
- **RFU-34** El presidente o el secretario puede crear y editar los horarios fijos de un grupo y generar sus reservas mes a mes.
- **RFU-35** El socio activo puede solicitar el arriendo de la sede y adjuntar su comprobante de pago.
- **RFU-36** El presidente o el secretario puede validar el pago y la garantía de un arriendo para confirmarlo.
- **RFU-37** El presidente o el secretario puede cancelar una reserva, incluida una solicitud de arriendo pendiente.
- **RFU-38** El presidente o el secretario puede imprimir el horario semanal o mensual.
- **RFU-39** El presidente o el secretario puede configurar los parámetros del sistema: horario de la sede, duración de los bloques, vigencia del certificado, plazo de descarga, plazo de confirmación del arriendo y valor del arriendo y de la garantía.

### 5. Ingresos y egresos

- **RFU-40** El presidente o el tesorero puede registrar ingresos con su concepto, monto, medio de pago y comprobante, incluido el pago de un arriendo ya validado. La garantía del arriendo se registra por separado y solo el monto retenido por daños se convierte en ingreso.
- **RFU-41** El presidente o el tesorero puede clasificar cada cuota o aporte como ordinaria o extraordinaria.
- **RFU-42** El presidente o el tesorero puede registrar la garantía de un arriendo y cambiar su estado (recibida, devuelta, por devolver, retenida por daños).
- **RFU-43** El presidente o el tesorero puede ingresar manualmente el monto retenido por daños.
- **RFU-44** El presidente o el tesorero puede registrar egresos (servicios básicos y mantención) con su comprobante.
- **RFU-45** El presidente o el tesorero puede devolver una cuota indicando un motivo.
- **RFU-46** El presidente o el tesorero puede anular un movimiento indicando un motivo.
- **RFU-47** El presidente o el tesorero puede consultar el saldo de caja.

### 6. Inventario

- **RFU-48** El presidente o el secretario puede registrar ítems del inventario con su cantidad.
- **RFU-49** El presidente o el secretario puede registrar préstamos de ítems a una organización registrada o a un nombre libre, con su responsable.
- **RFU-50** El presidente o el secretario puede registrar la devolución de un préstamo y el estado de regreso.

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
- **RFU-64** Todo usuario puede eliminar su propia cuenta desde su perfil.

## Requisitos funcionales de sistema (RFS)

Los 89 RFS traducen los RFU en comportamiento verificable del sistema y fijan las reglas de negocio acordadas.

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
- **RFS-16** El sistema deberá permitir dar de baja a un socio (estado "inactivo") con motivo y fecha obligatorios, y reactivarlo.
- **RFS-17** El sistema deberá retirar de inmediato a un socio dado de baja los accesos propios de socio, como las actas de asamblea y las solicitudes en línea de certificado y de arriendo.
- **RFS-18** El sistema deberá registrar en cada cambio sobre socios su autor, fecha y el dato modificado.

### 2. Certificados

- **RFS-19** El sistema deberá permitir a un socio activo solicitar un certificado de residencia en línea, con destinatario y comprobante de transferencia adjunto, y exigir el pago obligatorio de $1.000.
- **RFS-20** El sistema deberá limitar a 3 las solicitudes de certificado pendientes por usuario.
- **RFS-21** El sistema deberá permitir al presidente o al secretario validar el pago y emitir el certificado.
- **RFS-22** El sistema deberá permitir al presidente o al secretario rechazar una solicitud de certificado con motivo obligatorio.
- **RFS-23** El sistema deberá permitir al presidente o al secretario anular un certificado emitido con motivo obligatorio.
- **RFS-24** El sistema deberá habilitar la descarga del certificado en el perfil del socio durante 5 días desde su emisión (plazo configurable) y retirar el enlace al vencer ese plazo.
- **RFS-25** El sistema deberá registrar la fecha y hora de cada descarga del certificado y mostrarlas al presidente o al secretario.
- **RFS-26** El sistema deberá permitir retirar presencialmente un certificado emitido que el socio no descargó dentro del plazo, sin extender su vigencia. Si existe una descarga registrada, deberá exigir una nueva solicitud con nuevo pago.
- **RFS-27** El sistema deberá permitir al presidente o al secretario emitir un certificado de residencia a un vecino no socio, tras verificar su carnet, boleta de servicio básico y 6 meses de residencia, registrando sus datos, el destinatario y el pago (talonario o número de transacción).
- **RFS-28** El sistema deberá asignar a cada certificado un folio único y correlativo por año (formato AAAA-NNNN) al emitirlo, sin consumir folios en las solicitudes rechazadas.
- **RFS-29** El sistema deberá asignar a cada certificado un código de verificación aleatorio, distinto del folio, para su validación pública.
- **RFS-30** El sistema deberá guardar en cada certificado los datos del solicitante, las fechas de emisión y caducidad, el folio, el código de verificación, el nombre de quien lo otorga y el destinatario.
- **RFS-31** El sistema deberá fijar las fechas de emisión y de caducidad al emitirse el certificado, con una vigencia de 90 días configurable. La descarga o el retiro no modifican la vigencia.
- **RFS-32** El sistema deberá permitir registrar la fecha de retiro del certificado.
- **RFS-33** [Eliminado]
- **RFS-34** [Eliminado]
- **RFS-35** El sistema deberá ofrecer una página pública de validación donde, con el código de verificación, cualquiera pueda comprobar la autenticidad y el estado de un certificado (vigente, vencido o anulado), sin iniciar sesión, mostrando solo los datos necesarios con el RUT enmascarado y limitando la cantidad de consultas por minuto.
- **RFS-36** El sistema deberá generar el certificado en PDF y marcarlo con una marca de agua de borrador mientras no esté emitido.
- **RFS-37** El sistema deberá registrar automáticamente el pago de $1.000 como ingreso con concepto "certificados" al emitirse el certificado, sin requerir que quien lo emite tenga acceso al módulo de finanzas.

### 3. Grupos y organizaciones

- **RFS-38** El sistema deberá permitir crear y editar grupos con nombre y horarios de uso de la sede.
- **RFS-39** El sistema deberá asociar a cada grupo un único representante vigente, que sea socio activo y tenga cuenta de usuario.
- **RFS-40** El sistema deberá copiar al grupo los datos de contacto del representante (nombre, correo, teléfono, dirección) y permitir editarlos sin alterar la ficha del representante.
- **RFS-41** El sistema deberá dejar al grupo sin representante cuando su representante sea dado de baja como socio, hasta que se asigne uno nuevo.
- **RFS-42** Mientras un grupo no tenga representante, el sistema no deberá generar ni aceptar nuevas reservas ni arriendos a nombre del grupo. Las reservas ya generadas se mantienen.

### 4. Calendario de espacios

- **RFS-43** El sistema deberá mostrar un calendario digital con las reservas de la sede.
- **RFS-44** El sistema deberá registrar en cada reserva: día, hora, motivo, tipo, quién reserva y quién la ingresa. El ingreso manual ofrecerá una única lista fija: actividad de grupo, actividad particular gratuita y actividad de la junta; el motivo se completará automáticamente desde esa selección. La actividad particular se asociará a un socio activo responsable. Los arriendos mantienen su flujo separado de pago y garantía (RFS-50 a RFS-54).
- **RFS-45** El sistema deberá limitar las reservas al horario de la sede (por defecto de 09:00 a 22:00) en bloques de 1 hora, ambos configurables.
- **RFS-46** El sistema deberá rechazar cualquier reserva que se superponga con un horario ocupado.
- **RFS-47** El sistema deberá modelar el horario fijo de un grupo como reserva recurrente, cuyas reservas genera cada mes el presidente o el secretario y que se confirman automáticamente.
- **RFS-48** El sistema no deberá permitir que un arriendo se superponga con un horario fijo de grupo.
- **RFS-49** El sistema deberá permitir al presidente o al secretario editar los horarios fijos de los grupos.
- **RFS-50** El sistema deberá permitir a socios activos solicitar un arriendo y rechazar la solicitud de vecinos sin inscripción activa. Se adjuntará un único comprobante por el total del arriendo y la garantía.
- **RFS-51** El sistema deberá dejar el arriendo en estado "pendiente" y bloquear su horario hasta que el presidente o el secretario valide el pago y la garantía.
- **RFS-52** El sistema deberá liberar automáticamente el horario de un arriendo pendiente si nadie valida el pago dentro del plazo configurable (por defecto 48 horas desde la solicitud), sin perjuicio de la cancelación manual.
- **RFS-53** El sistema deberá confirmar el arriendo cuando el presidente o el secretario valide el pago y la garantía. El registro posterior en finanzas no condiciona la reserva.
- **RFS-54** El sistema deberá permitir cancelar una reserva y, si la garantía asociada ya estaba recibida, marcarla como "por devolver".
- **RFS-55** El sistema deberá mostrar a los socios los horarios como libres u ocupados, sin detalle.
- **RFS-56** El sistema deberá mostrar al presidente y al secretario el detalle de cada reserva.
- **RFS-57** El sistema deberá permitir al presidente o al secretario imprimir el horario semanal y mensual.

### 5. Ingresos y egresos

- **RFS-58** El sistema deberá registrar cada ingreso con concepto (arriendo de sede, cuota de socios, aporte de grupos, certificados, otros), fecha de pago, monto, medio de pago (efectivo o transferencia), quién lo ingresa y comprobante.
- **RFS-59** El sistema deberá exigir, para pagos en efectivo, el número de talonario y, para transferencias, la copia o el número de transacción.
- **RFS-60** El sistema deberá exigir clasificar toda cuota o aporte como ordinaria o extraordinaria, sin asociarla a proyectos.
- **RFS-61** El sistema deberá registrar el arriendo con un valor de $40.000 más $40.000 de garantía (valores configurables).
- **RFS-62** El sistema deberá gestionar la garantía de forma separada de los ingresos, con estados: recibida, devuelta, por devolver y retenida por daños.
- **RFS-63** El sistema deberá convertir la garantía en ingreso solo cuando su estado sea "retenida por daños", usando el monto ingresado manualmente, sin superar la garantía recibida. Se permitirá retención parcial y devolución del resto en una misma operación, guardando ambos montos, fecha, motivo y autor.
- **RFS-64** El sistema deberá permitir al presidente o al tesorero registrar el pago de un arriendo ya validado como ingreso y su garantía como registro separado, conforme a RFS-62 y RFS-63.
- **RFS-65** El sistema deberá registrar egresos (servicios básicos y mantención) con fecha, número de transacción, copia del comprobante y quién pagó.
- **RFS-66** El sistema deberá permitir devolver una cuota con motivo obligatorio y descontar el monto de la caja.
- **RFS-67** El sistema no deberá permitir borrar movimientos; solo anularlos con motivo obligatorio.
- **RFS-68** El sistema deberá calcular y mostrar el saldo de caja a partir de ingresos, egresos y devoluciones.

### 6. Inventario

- **RFS-69** El sistema deberá registrar ítems con nombre y cantidad.
- **RFS-70** El sistema deberá registrar préstamos con ítem, cantidad, estado de salida y de regreso, fechas de solicitud, préstamo y devolución, y responsable (nombre y teléfono).
- **RFS-71** El sistema deberá permitir asignar el préstamo a una organización registrada o a un nombre libre.
- **RFS-72** El sistema deberá impedir prestar más unidades de las disponibles.

### 7. Actas

- **RFS-73** El sistema deberá registrar actas con fecha, tipo (asamblea o directiva), acuerdos en texto y escaneo opcional.
- **RFS-74** El sistema deberá mostrar las actas de asamblea solo a socios activos y dirigentes.
- **RFS-75** El sistema deberá mostrar las actas de directiva solo a dirigentes (presidente, secretario y tesorero).

### 8. Proyectos

- **RFS-76** El sistema deberá permitir al presidente registrar y editar proyectos con título, organismo, fecha de postulación, estado (postulado, adjudicado o rechazado) y monto opcional.
- **RFS-77** El sistema deberá registrar quién ingresó el proyecto en la plataforma del organismo y completar automáticamente quién lo registró en el sistema.
- **RFS-78** El sistema deberá mostrar los proyectos en modo solo lectura a todo usuario con sesión iniciada, sin posibilidad de postular.

### 9. Reportes

- **RFS-79** El sistema deberá generar reportes de pagos recibidos, egresos y arriendos por periodo y por organización, disponibles para el tesorero y el presidente.
- **RFS-80** El sistema deberá generar el listado de socios en Excel y PDF, disponible para el secretario y el presidente.

### 10. Roles, permisos, auditoría, parámetros y cuenta

- **RFS-81** El sistema deberá restringir el acceso a cada módulo según el rol: presidente a todos; secretario a socios, citas, certificados, grupos, calendario, inventario y actas; tesorero a finanzas, actas y reportes financieros.
- **RFS-82** El sistema deberá autorizar las acciones propias de un socio (certificado de residencia en línea, actas de asamblea y solicitud de arriendo) según el estado activo de su ficha y no según su rol, de modo que un dirigente con ficha activa también pueda ejecutarlas.
- **RFS-83** El sistema deberá permitir al administrador gestionar usuarios y roles y ver el nombre, correo y rol de cada usuario, sin acceso a fichas de socios, finanzas ni actas de directiva.
- **RFS-84** El sistema no deberá permitir que un administrador cambie su propio rol.
- **RFS-85** El sistema deberá registrar fecha, autor, cambios y dirección IP en socios, certificados (incluidas las descargas), usuarios, finanzas y cuotas devueltas.
- **RFS-86** El sistema no deberá permitir editar ni eliminar los registros de trazabilidad.
- **RFS-87** El sistema deberá permitir solo al presidente consultar la auditoría, con filtros por fecha, autor y tipo de registro (socio, certificado, usuario o finanzas).
- **RFS-88** El sistema deberá permitir al presidente o al secretario configurar el horario de la sede, la duración de los bloques, la vigencia del certificado, el plazo de descarga, el plazo de confirmación del arriendo y el valor del arriendo y de la garantía.
- **RFS-89** El sistema deberá permitir a cada usuario eliminar su propia cuenta desde su perfil, conservando la ficha de socio y los registros de trazabilidad sin vínculo al usuario.
