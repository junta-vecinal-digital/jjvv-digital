# Requisitos del sistema

## Perfiles y permisos

| Perfil | Definición |
| --- | --- |
| Vecino | Residente de la unidad vecinal; es un rol de usuario. |
| Socio | Vecino con ficha activa; no es un rol adicional. |
| Representante | Persona designada por un grupo. |
| Dirigente | Presidente, secretario o tesorero. |
| Administrador | Gestiona usuarios, roles, parámetros y catálogos; no es dirigente. |

| Rol | Acceso |
| --- | --- |
| Presidente | Gestión vecinal, finanzas, actas, proyectos, reportes y auditoría. |
| Secretario | Socios, citas, atención al público, certificados, grupos, calendario, reservas, inventario, solicitudes y préstamos de insumos, y actas. |
| Tesorero | Finanzas, actas y reportes financieros. |
| Administrador | Usuarios, roles, parámetros y catálogos. Ve nombre, correo y rol; puede dar de baja fichas de dirigentes con motivo y fecha, sin acceso general a fichas, finanzas o actas de directiva. |
| Vecino y socio | Registro e inscripción; trámites personales y consultas según el estado de su ficha y los permisos específicos. |

Todos los usuarios con sesión iniciada pueden consultar proyectos. Los dirigentes necesitan ficha activa para sus trámites personales, conservando los permisos operativos del cargo durante su regularización. Cualquier vecino sin ficha ni cuenta puede solicitar sede o insumos en ventanilla con los antecedentes exigidos. «Presidente o secretario» significa que basta con uno de ellos.

## Requisitos funcionales de usuario (RFU)

### 1. Registro e inscripción de socios

- **RFU-01** El vecino puede registrarse en el sistema.
- **RFU-02** El vecino puede solicitar inscripción con RUT, declaración de al menos 6 meses de residencia, carnet, boleta de servicio básico y teléfono.
- **RFU-03** El vecino puede consultar el estado de su solicitud y el motivo de rechazo.
- **RFU-04** El vecino puede corregir y reenviar una solicitud reabierta.
- **RFU-05** El presidente o secretario puede revisar, aprobar o rechazar solicitudes con motivo.
- **RFU-06** El presidente o secretario puede reabrir solicitudes rechazadas.
- **RFU-07** El presidente o secretario puede habilitar horarios de citas de firma.
- **RFU-08** El vecino aprobado puede agendar una cita para firmar el libro de socios.
- **RFU-09** El vecino puede cancelar su cita futura.
- **RFU-10** El presidente o secretario puede liberar citas reservadas y eliminar horarios sin reserva.
- **RFU-11** El presidente o secretario puede activar al socio tras la firma, registrando su fecha de ingreso.
- **RFU-12** El presidente o secretario puede inscribir en ventanilla con estado por firmar o activo; este último exige fecha de ingreso.
- **RFU-13** El presidente o secretario puede consultar el carnet y la boleta adjuntos.
- **RFU-14** El presidente o secretario puede buscar socios y editar sus datos.
- **RFU-15** El presidente o secretario puede dar de baja con motivo y fecha y reactivar a socios no dirigentes. Solo el administrador puede dar de baja la ficha de un dirigente, conservando sus permisos de cargo.
- **RFU-16** El presidente o secretario puede crear la cuenta de un socio de ventanilla con correo y contraseña.

### 2. Certificados

- **RFU-17** El socio puede solicitar residencia en línea con destinatario y aporte voluntario, incluido $0; solo el aporte positivo exige respaldo de transferencia.
- **RFU-18** El socio puede descargar su certificado de residencia durante 5 días desde la emisión; después desaparece el enlace.
- **RFU-19** El socio puede retirar en sede el certificado no descargado a tiempo. Si ya lo descargó, requiere otra solicitud con aporte voluntario.
- **RFU-20** [Eliminado; emisión de certificado de socio en RFU-65].
- **RFU-21** El no socio puede solicitar residencia en sede acreditando 6 meses de residencia, con aporte voluntario en efectivo o transferencia, incluido $0.
- **RFU-22** El presidente o secretario puede verificar carnet, boleta y residencia del no socio, dejando constancia.
- **RFU-23** El presidente o secretario puede emitir residencia, verificando el aporte únicamente si es positivo.
- **RFU-24** El presidente o secretario puede rechazar solicitudes con motivo.
- **RFU-25** El presidente o secretario puede anular certificados con motivo.
- **RFU-26** El presidente o secretario puede registrar la fecha de retiro.
- **RFU-27** El presidente o secretario puede consultar si el socio descargó el certificado.
- **RFU-28** Cualquier persona puede validar certificados por código, sin cuenta.
- **RFU-65** El socio activo puede emitir su certificado de socio autónomamente; presidente o secretario puede emitirlo en ventanilla solo para impresión.

### 3. Grupos y organizaciones

- **RFU-29** El presidente o secretario puede crear y editar grupos y sus horarios de sede.
- **RFU-30** El presidente o secretario puede registrar y editar representantes con autocompletado desde socios o dirigentes, o datos manuales, sin exigir ficha ni cuenta.

### 4. Calendario y reservas de sede

- **RFU-31** El socio puede consultar horarios libres u ocupados.
- **RFU-32** El presidente o secretario puede consultar el detalle de reservas.
- **RFU-33** El presidente o secretario puede gestionar reservas de grupos, particulares y junta, con o sin aporte, e ingresarlas en ventanilla sin ficha ni cuenta. El calendario permite consultar ocupación.
- **RFU-34** El presidente o secretario puede editar horarios fijos de grupos y generar sus reservas mensuales.
- **RFU-35** El socio activo puede solicitar sede en línea y adjuntar el comprobante correspondiente al aporte informado.
- **RFU-36** El presidente o secretario puede definir aporte voluntario y garantía opcional por reserva, incluidos $0, y verificar su recepción con comprobante total, sin crear aún el ingreso contable.
- **RFU-37** El presidente o secretario puede cancelar reservas y solicitudes pendientes.
- **RFU-38** El presidente o secretario puede imprimir horarios semanales o mensuales.
- **RFU-39** Solo el administrador puede configurar horarios, bloques, vigencia y descarga de certificados, plazo de confirmación de sede y catálogos. Aportes y garantías se definen por operación, sin tarifas fijas.

### 5. Ingresos y egresos

- **RFU-40** El presidente o tesorero puede registrar ingresos con concepto, monto, medio y comprobante, incluidos sede e insumos. Las garantías se separan del ingreso; solo la retención por daños genera ingreso. No se crean ingresos de $0.
- **RFU-41** El presidente o tesorero puede clasificar cuotas de socios y aportes de grupos como ordinarios o extraordinarios; sede y préstamos usan conceptos propios.
- **RFU-42** El presidente o tesorero puede gestionar garantías de insumos y sede hasta su resolución: recibida, devuelta, por devolver o retenida por daños.
- **RFU-43** El presidente o tesorero puede indicar manualmente el monto retenido por daños.
- **RFU-44** El presidente o tesorero puede registrar egresos de servicios y mantención con comprobante.
- **RFU-45** El presidente o tesorero puede devolver cuotas con motivo.
- **RFU-46** El presidente o tesorero puede anular movimientos con motivo.
- **RFU-47** El presidente o tesorero puede consultar el saldo de caja.

### 6. Inventario y préstamos de insumos

- **RFU-48** El presidente o secretario puede registrar insumos del catálogo y editar existencias sin bajar de las unidades comprometidas o fuera de servicio.
- **RFU-49** El presidente o secretario puede gestionar solicitudes e ingresar vecinos en ventanilla sin ficha ni cuenta, con nombre, RUT válido, domicilio y teléfono; identificará la agrupación del representante seleccionado. El responsable será automático y el aporte total y garantía se definirán por préstamo, admitiendo aporte $0.
- **RFU-50** El presidente o secretario puede registrar entrega y devolución completa, estados de salida/regreso, daños y consultar atrasos.
- **RFU-67** El socio activo, incluido el dirigente con ficha activa, puede solicitar varios insumos y cantidades en línea, indicando su agrupación si es representante y recibiendo un recordatorio de horarios de retiro.

### 7. Actas

- **RFU-51** El presidente o secretario puede registrar actas de asamblea y directiva con escaneo opcional.
- **RFU-52** El socio activo puede consultar actas de asamblea.
- **RFU-53** Los dirigentes pueden consultar actas de asamblea y directiva.

### 8. Proyectos

- **RFU-54** El presidente puede registrar y editar proyectos postulados por la junta.
- **RFU-55** Todo usuario con sesión puede consultar proyectos y estados en modo lectura.

### 9. Reportes

- **RFU-56** El presidente o tesorero puede generar reportes de pagos, egresos y arriendos por período y organización.
- **RFU-57** El presidente o secretario puede obtener listados de socios en Excel o PDF.

### 10. Auditoría

- **RFU-58** El presidente puede consultar autor y fecha de cambios en datos de socios.
- **RFU-59** El presidente puede consultar emisión, descarga, retiro y anulación de certificados.
- **RFU-60** El presidente puede consultar trazabilidad financiera y garantías, filtrando por fecha, autor y tipo de movimiento.
- **RFU-61** El presidente puede consultar altas y cambios de rol y correo de usuarios.

### 11. Administración, cuenta y atención al público

- **RFU-62** El administrador puede gestionar usuarios y consultar nombre, correo y rol.
- **RFU-63** El administrador puede gestionar roles.
- **RFU-64** El vecino puede eliminar su cuenta. Solo un administrador puede eliminar cuentas de dirigentes, conservando ficha e historial; ningún administrador puede eliminar su propia cuenta.
- **RFU-66** El presidente o secretario puede gestionar horarios públicos semanales, con varios tramos diarios, visibles en portada e independientes de las citas de firma.

## Requisitos funcionales de sistema (RFS)

### 1. Registro, inscripción y socios

- **RFS-01** Registrar vecinos con nombre, correo y contraseña, asignando rol vecino.
- **RFS-02** Exigir RUT con dígito verificador válido y rechazar uno ya registrado como socio, indicando acudir a sede.
- **RFS-03** Exigir RUT, declaración de 6 meses de residencia, carnet, boleta y teléfono antes de enviar la inscripción.
- **RFS-04** Manejar fichas pendientes, por firmar, activas, rechazadas e inactivas.
- **RFS-05** Exigir motivo de rechazo y mostrarlo al vecino.
- **RFS-06** Permitir a presidente o secretario reabrir rechazos como pendientes para corregir y reenviar.
- **RFS-07** Presidente/secretario crea citas de 15, 20, 30, 45 o 60 minutos hasta 6 meses adelante; fichas por firmar ven los próximos 30 días.
- **RFS-08** Permitir una cita por vecino aprobado y bloquear el horario para otros.
- **RFS-09** Permitir cancelar citas futuras y liberar el bloque.
- **RFS-10** Permitir a presidente o secretario liberar citas reservadas y eliminar bloques sin reserva.
- **RFS-11** Permitir a presidente o secretario activar la ficha tras la firma, con fecha de ingreso.
- **RFS-12** Registrar socios en ventanilla sin cuenta, por firmar o activos con fecha de ingreso; correo y adjuntos opcionales, RUT y 6 meses de residencia validados.
- **RFS-13** Permitir a presidente o secretario consultar carnet y boleta adjuntos.
- **RFS-14** Permitir a presidente o secretario buscar por nombre/RUT, filtrar por estado y editar datos; revalidar residencia solo si cambia su fecha.
- **RFS-15** Permitir a presidente o secretario crear una cuenta de rol vecino con correo y contraseña y vincularla a la ficha de ventanilla.
- **RFS-16** Permitir baja con motivo y fecha y reactivación de socios no dirigentes; reservar al administrador la baja de dirigentes desde Usuarios, incluida la prohibición de autobaja para presidente y secretario.
- **RFS-17** Retirar accesos de socio al darlo de baja, conservando permisos de cargo; exigir ficha activa también a dirigentes para solicitudes personales de insumos.
- **RFS-18** Registrar autor, fecha y dato modificado en cambios de socios.
- **RFS-102** Exigir a dirigentes ficha y antecedentes validados como a los demás socios, reutilizando registros. Permitir regularizar inscripción y cita sin perder permisos de cargo; otro presidente o secretario resolverá la inscripción, sin autoaprobación, activación automática ni duplicados.

### 2. Certificados

- **RFS-19** Solicitar residencia en línea con ficha activa, destinatario y aporte voluntario ≥ $0; Parámetros solo sugiere el importe. Guardar el elegido; si es positivo exigir comprobante y número de transferencia, y con $0 no exigir respaldo ni crear ingreso.
- **RFS-20** Limitar a 3 solicitudes pendientes por usuario.
- **RFS-21** Permitir a presidente o secretario emitir tras revisar documentos y verificar únicamente aportes positivos; admitir emisión sin aporte.
- **RFS-22** Permitir a presidente o secretario rechazar solicitudes con motivo obligatorio.
- **RFS-23** Permitir a presidente o secretario anular certificados con motivo obligatorio.
- **RFS-24** Habilitar descarga por 5 días desde emisión, configurables, y retirar el enlace al vencer.
- **RFS-25** Registrar fecha/hora de cada descarga de residencia y mostrarla a presidente/secretario; excluir descargas de certificado de socio del registro y auditoría.
- **RFS-26** Permitir retiro del certificado no descargado a tiempo sin ampliar vigencia; si fue descargado, exigir nueva solicitud con aporte voluntario.
- **RFS-27** Permitir a presidente o secretario emitir residencia al no socio verificando carnet, boleta y 6 meses de residencia; registrar datos, destinatario y aporte, incluido $0. Solo un aporte positivo exige recepción verificada y talonario/transacción.
- **RFS-28** Asignar folio único correlativo anual AAAA-NNNN al emitir, sin consumir folios en rechazos.
- **RFS-29** Asignar código aleatorio de validación pública distinto del folio.
- **RFS-30** Guardar solicitante, fechas, folio, código, destinatario y copia del nombre/cargo del otorgante al emitir; conservarlos en el PDF pese a cambios de cuenta. Mantener históricos sin identidad registrada e informar su ausencia sin inventarla.
- **RFS-31** Fijar emisión y caducidad al emitir, con vigencia configurable de 90 días, sin alterarla por descarga o retiro.
- **RFS-32** Registrar la fecha de retiro.
- **RFS-33** [Eliminado; consultar RFS-90].
- **RFS-34** [Eliminado; consultar RFS-90].
- **RFS-35** Validar públicamente autenticidad y estado vigente/vencido/anulado por código, sin sesión, con datos mínimos, RUT enmascarado y límite de consultas por minuto.
- **RFS-36** Generar PDF con marca de borrador hasta la emisión.
- **RFS-37** Al emitir residencia, registrar automáticamente el aporte positivo guardado como ingreso de certificados, sin permiso financiero del emisor ni recalcular desde Parámetros. No crear ingresos de $0.
- **RFS-90** Emitir certificado de socio inmediatamente desde cuenta con ficha activa y presidente vigente como otorgante; en ventanilla, presidente/secretario lo emitirá solo para impresión, sin descargas personales. Residencia conserva revisión documental y verificación solo de aportes positivos.

### 3. Grupos y organizaciones

- **RFS-38** Crear y editar grupos con nombre y horarios de sede.
- **RFS-39** Asignar un representante vigente por grupo, sin ficha ni cuenta obligatorias, con autocompletado o ingreso manual y RUT válido obligatorio. Conservar históricos sin RUT y exigir completarlo al volver a operar.
- **RFS-40** Copiar nombre, correo, teléfono y dirección del representante al grupo y permitir editarlos sin cambiar su ficha.
- **RFS-41** Conservar representantes dados de baja como socios; retirarlos o reemplazarlos solo por acción explícita.
- **RFS-42** Impedir nuevas reservas del grupo sin representante, conservando las existentes.
- **RFS-94** Mostrar etiqueta informativa de representante en fichas existentes, sin conceder permisos ni exigir ficha/cuenta a representantes externos.

### 4. Calendario y reservas de sede

- **RFS-43** Mostrar ocupación en el calendario y gestionar reservas por separado en Reservas, con apartado de insumos.
- **RFS-44** Registrar día, hora, actividad, solicitante y autor; derivar el motivo de la actividad. Administrador gestiona catálogo conservando nombres históricos; reservas privadas sin ficha solo en ventanilla. Aplicar catálogo inicial de RFS-106 y aporte $0 a actividades adicionales.
- **RFS-45** Limitar reservas al horario de sede y bloques configurables, por defecto 09:00–22:00 y 1 hora.
- **RFS-46** Rechazar reservas superpuestas.
- **RFS-47** Generar mensualmente, por presidente o secretario, las reservas recurrentes de grupos con confirmación automática.
- **RFS-48** Impedir arriendos superpuestos con horarios fijos de grupos.
- **RFS-49** Permitir a presidente o secretario editar horarios fijos de grupos.
- **RFS-50** Admitir sede en línea para socios activos, incluidos dirigentes, y en ventanilla sin ficha/cuenta con nombre, RUT válido, domicilio y teléfono. Presidente/secretario define aporte y garantía opcional; total positivo exige comprobante de ambos, adjuntado por titular en línea o directiva en ventanilla; total $0 no exige respaldo. Separar garantía del ingreso.
- **RFS-51** Mantener pendientes y bloquear horarios de solicitudes con aporte o garantía hasta confirmación; conservar registro directo gratuito y reservas recurrentes.
- **RFS-52** Liberar automáticamente solicitudes pendientes no confirmadas en 48 horas, configurables, además de permitir cancelación manual.
- **RFS-53** Presidente/secretario confirma verificando importes positivos con comprobante total; ambos $0 permiten confirmar sin respaldo. Confirmar no crea ingresos ni depende del registro financiero posterior.
- **RFS-54** Cancelar reservas liberando el horario y dejar garantías recibidas por devolver, conservando su resolución financiera.
- **RFS-55** Mostrar a socios solo ocupación libre u ocupada.
- **RFS-56** Mostrar detalles de reservas a presidente y secretario.
- **RFS-57** Permitir a presidente o secretario imprimir horarios semanales y mensuales.
- **RFS-106** Iniciar actividades con Actividad de la junta, comunitaria, de agrupación, privada y Taller, sin «Reserva de sede». Habilitar «En representación de» solo para actividad de agrupación, mostrando grupos y limpiando el vínculo al cambiar. En línea exigir ficha activa y representación del grupo elegido; conservar catálogo administrable, históricos y actividades adicionales gratuitas.

### 5. Ingresos, egresos y garantías

- **RFS-58** Registrar concepto, fecha de pago, monto, medio efectivo/transferencia, autor y comprobante; conceptos: sede, insumos, cuotas, grupos, certificados y otros. Conservar conceptos históricos sin reclasificación automática.
- **RFS-59** Exigir talonario en efectivo y archivo o número de transacción en transferencias. Permitir respaldo manual PDF/JPG/PNG de hasta 5 MB, privado para presidente/tesorero y conservado tras anulación.
- **RFS-60** Clasificar cuotas de socios y aportes de grupos como ordinarios/extraordinarios, sin asociarlos a proyectos; sede e insumos usan conceptos propios sin esa clasificación.
- **RFS-61** Presidente/secretario define aporte voluntario y garantía opcional de sede por operación, ambos desde $0, sin tarifas fijas; separar garantía del ingreso y conservar condiciones históricas.
- **RFS-62** Gestionar garantías de sede e insumos, nuevas o históricas, fuera de ingresos: recibida, devuelta, por devolver o retenida por daños.
- **RFS-63** Convertir en ingreso solo la retención por daños indicada manualmente, sin superar lo recibido; admitir retención parcial y devolución restante juntas, con montos, fecha, motivo y autor.
- **RFS-64** Permitir a presidente/tesorero registrar aportes de sede y cobros de insumos vinculados a la operación; registrar garantías aparte, sin ingresos de $0 y conservando trazabilidad histórica.
- **RFS-65** Registrar egresos de servicios y mantención con fecha, transacción, comprobante y persona que pagó.
- **RFS-66** Devolver cuotas con motivo obligatorio y descontarlas de caja.
- **RFS-67** Impedir borrar movimientos y permitir solo anulación con motivo.
- **RFS-68** Calcular saldo desde ingresos, egresos y devoluciones.
- **RFS-104** Presentar importes recibidos de certificados, sede, préstamos, socios y grupos como aportes voluntarios comunitarios; identificar garantías como devolvibles y egresos como gastos, sin reclasificar ni recalcular históricos.
- **RFS-108** Mostrar a presidente/tesorero Ingresos por aprobar dentro de Ingresos, con reservas previamente validadas, incluso canceladas, y préstamos aprobados, entregados o devueltos con importes pendientes. Consultar identidad, importes y respaldo/recepción y registrar desde filas desplegables aporte positivo y garantía separada; excluir ambos $0 y operaciones registradas, admitir reingreso de aportes anulados sin duplicar garantías. Resolver garantías en Finanzas.

### 6. Inventario y préstamos de insumos

- **RFS-69** Registrar insumos del catálogo y cantidad total; permitir a presidente/secretario editarla según RFS-101.
- **RFS-70** Registrar varios insumos por préstamo, cantidades y estados de salida/regreso; fechas de solicitud, entrega y devolución comprometida/efectiva; solicitante, contacto y grupo cuando corresponda. Distinguir responsable automático presidente/secretario del solicitante y conservar nombre/cargo históricos.
- **RFS-71** Permitir solicitudes en línea de socios activos, incluidos dirigentes, y en ventanilla de cualquier vecino con nombre, RUT válido, domicilio y teléfono, sin ficha/cuenta. Exigir grupo a representantes seleccionados, no a particulares manuales; admitir varios insumos y cantidades.
- **RFS-72** Validar stock en servidor y ante simultaneidad al aprobar/entregar. Pendientes no reservan; aprobadas reservan todas las líneas. Disponibles = total − reservadas sin entregar − prestadas sin devolver − fuera de servicio, sin doble descuento.
- **RFS-95** Administrador amplía el catálogo por categorías: mobiliario/eventos (sillas, mesas, toldos, carpas estructurales, vajilla, mantelería), equipamiento (amplificación), herramientas (jardinería, eléctricas, escalas, escaleras extensibles) y ayudas técnicas en comodato (catres clínicos, sillas de ruedas, burritos, muletas, bastones). Selección desplegable, sin precios ni garantías unitarias.
- **RFS-96** Manejar pendiente, aprobado con reserva, entregado y devuelto; presidente/secretario aprueba todas las líneas y registra luego la entrega sin doble descuento. Consultar o solicitar no habilita autoaprobación; gestionar exige permiso de presidente/secretario.
- **RFS-97** Presidente/secretario define monto total, incluido $0, y garantía global por préstamo, sin multiplicar por unidades, tipos o días; guardar fuera del catálogo y separar garantía del cobro.
- **RFS-98** Recordar horarios públicos de retiro sin crear citas. Marcar atrasados los entregados no devueltos cuya fecha comprometida ya pasó; indicar atrasos y pendientes a presidente/secretario con puntos rojos en sus accesos/bandejas, sin contadores, correos ni lectura.
- **RFS-99** Registrar devolución completa única, sin parciales, con fecha efectiva, estado, daños por insumo y autor. Liberar utilizables y dejar dañados fuera de servicio, sin superar lo prestado; sin reparación/reincorporación en esta entrega.
- **RFS-101** Permitir a presidente/secretario editar totales no negativos ni inferiores a reservados, prestados y fuera de servicio; comprobar consistencia simultánea con aprobación, entrega y devolución, conservando historial.
- **RFS-107** Asignar automáticamente responsable presidente/secretario: quien registra en ventanilla o aprueba en línea, sin selector. Conservar nombre/cargo aunque cambie su cuenta/rol y no reasignar préstamos históricos aprobados.

### 7. Actas

- **RFS-73** Registrar fecha, tipo asamblea/directiva, acuerdos y escaneo opcional.
- **RFS-74** Mostrar actas de asamblea solo a socios activos y dirigentes.
- **RFS-75** Mostrar actas de directiva solo a presidente, secretario y tesorero.

### 8. Proyectos

- **RFS-76** Permitir al presidente registrar/editar título, organismo, fecha de postulación, estado postulado/adjudicado/rechazado y monto opcional.
- **RFS-77** Seleccionar un dirigente como postulador externo al crear o cambiarlo, sin texto libre; registrar automáticamente quién ingresa el proyecto, pudiendo ser la misma persona. Conservar nombres históricos al consultar o editar otros datos, sin reasignación automática.
- **RFS-78** Mostrar proyectos a usuarios con sesión en modo lectura, sin permitir postular.

### 9. Reportes

- **RFS-79** Generar reportes de pagos, egresos y arriendos por período y organización para presidente/tesorero.
- **RFS-80** Generar listados de socios en Excel/PDF para presidente/secretario.

### 10. Roles, cuenta, auditoría y parámetros

- **RFS-81** Aplicar los permisos de la tabla inicial; reservar usuarios, roles, parámetros y catálogos al administrador y auditoría al presidente. Solicitudes personales de dirigentes no conceden gestión de inventario ni préstamos ajenos al tesorero; administrador solo puede operar fichas para baja de dirigentes.
- **RFS-82** Autorizar trámites de socio por ficha activa, no por rol; exigirla también a dirigentes para residencia, sede e insumos, conservando permisos operativos del cargo.
- **RFS-83** Reservar usuarios, roles y parámetros al administrador, con acceso a nombre/correo/rol pero no a fichas, finanzas ni actas de directiva, salvo baja de dirigente con motivo y fecha.
- **RFS-84** Impedir que el administrador cambie su propio rol.
- **RFS-85** Auditar fecha, autor, cambios e IP en socios, certificados, usuarios, finanzas y cuotas devueltas; incluir descargas de residencia y excluir las de certificado de socio.
- **RFS-86** Impedir editar o eliminar trazabilidad.
- **RFS-87** Reservar consulta de auditoría al presidente con filtros por fecha, autor y tipo socio/certificado/usuario/finanzas.
- **RFS-88** Reservar al administrador horario de sede, bloques, vigencia/descarga de certificados, plazo de confirmación y catálogos. No fijar tarifas de sede ni precios unitarios: presidente/secretario define aportes y garantías por operación.
- **RFS-89** Permitir al vecino eliminar su cuenta y al administrador otras cuentas, conservando fichas e historial sin vínculo al usuario. Prohibir autoeliminación a dirigentes y administradores.
- **RFS-91** Presidente/secretario define atención pública semanal con varios tramos por día, visible en portada; citas independientes en horarios permitidos. Al aprobar documentos recordar liberar citas, sin crearlas ni restringirlas a la atención.

### 11. Navegación, presentación y conservación histórica

- **RFS-92** Reservas con pestañas de sede e insumos; Inventario separado con Registrar insumo/Historial de préstamos. Finanzas con Ingresos y egresos, pendientes dentro de Ingresos y Registro garantías conjunto; conservar atención, Mi cuenta y auditoría al final.
- **RFS-93** Colocar el control mostrar/ocultar contraseña junto al campo en la edición administrativa de usuarios.
- **RFS-100** Conservar reservas, pagos, garantías, préstamos y proyectos históricos sin borrar ni recalcular montos, autores o condiciones. Mantener resolución de garantías de sede e historial anterior, sin habilitar nuevas operaciones contrarias a reglas vigentes.
- **RFS-103** Permitir elegir mes/año y mostrar dd/mm/aaaa independientemente del navegador; separar texto y flecha en desplegables, usar identidad JJVV Digital en acceso, destacar verificación pública y ocultar filtros de proyectos.
- **RFS-105** Omitir en reservas el texto sobre destino comunitario y último paso del aporte; ocultar a presidente/secretario los horarios de retiro de insumos, conservando el recordatorio para solicitantes personales.
