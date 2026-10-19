# Historial de sprints

## Sprint 4: correcciones de navegación, ventanilla y protección de dirigentes — 19/10/2026

- Autorización específica del usuario para actualizar requisitos: Inventario conserva registro/edición y reúne historial de consulta fuera de Reservas; se retiran accesos cruzados del formulario de sede. Operaciones de insumos en su bandeja, incluidos entregados pendientes de devolución. Préstamos anteriores abiertos siguen operándose desde esa bandeja; historial y detalles abiertos desde él son de consulta.
- Actividades iniciales: junta, comunitaria, agrupación, privada y taller. Migración 000015 actualiza catálogo sin modificar nombres guardados en reservas existentes. El selector En representación de se habilita solo para agrupaciones y se limpia al cambiar de actividad. Se conserva validación del representante; particulares en ventanilla no exigen ficha. Actividades adicionales mantienen aporte cero.
- Préstamos de insumos en ventanilla para cualquier vecino con nombre, RUT validado, domicilio y teléfono, sin crear ficha/cuenta ni exigir agrupación. Solicitudes en línea conservan ficha activa. Se ocultan horarios de retiro a presidente/secretario y el texto indicado sobre aportes en sede.
- Solo administrador puede dar de baja ficha de dirigente desde Usuarios con motivo/fecha. Dirigentes y administradores no eliminan su propia cuenta desde perfil; administrador puede eliminar otras cuentas con confirmación. Se conservan fichas y registros. Restricciones también en servidor, sin acceso administrativo general a fichas.
- Regresión preparada en Integracion/VentanillaInventarioYProteccionDeDirigentesTest.php. Caso existente ajustado para mantener entregados visibles en la bandeja operativa. No se ejecutan tests ni migraciones desde el agente; sintaxis, rutas y compilación se revisan. Migración, tests y revisión manual pendientes del usuario. Sin despliegue ni instalaciones.

---

## Sprint 4: continuación de optimización visual — 18/10/2026

- El usuario da conformidad inicial a las etapas 1 a 3 y autoriza continuar. Se mejoran presentación y orientación, conservando los flujos y permisos existentes.
- Etiquetas de estado compartidas en listados y detalles de reservas, solicitudes de insumos y préstamos entregados. El texto del estado se conserva, sin depender solo del color.
- Detalles de sede e insumos con encabezados uniformes y orientación del siguiente paso según estado y permisos operativos; no se añaden acciones ni validaciones nuevas.
- Formularios por pasos con avance numérico, botones que se adaptan al ancho disponible y sin recortar los selectores de fecha. Errores y confirmaciones de detalle con roles accesibles.
- Tablas con encabezados más legibles, alineación superior, enlaces reconocibles y resaltado de fila; foco de teclado visible y contención del ancho en zonas de tablas.
- Sin tests ejecutados por el agente, requisitos, migraciones, instalaciones ni despliegue. Sintaxis de vistas y compilación revisadas. Tests y comprobación manual de estados, formularios, modo oscuro, zoom y ventanas pequeñas pendientes del usuario. No se acredita revisión visual automatizada.

---

## Sprint 4: optimización visual, etapas 1 a 3 — 18/10/2026

- El usuario confirma los tests del ajuste anterior de reservas y navegación. Autoriza continuar con el plan visual; se implementan sus primeras tres etapas para revisión.
- Panel con bienvenida compacta y accesos por cargo: gestión vecinal para presidente/secretario y accesos financieros para presidente/tesorero. Se conservan las listas existentes de pendientes y sus permisos.
- Apartado activo más visible, foco de teclado en menú y flechas de desplegables que indican apertura. Se mantienen el orden y los módulos acordados.
- Encabezado compartido con título, orientación breve y acciones en socios, certificados, agrupaciones, ingresos, egresos, reservas e insumos. Acciones de ingresos/egresos trasladadas al encabezado sin duplicarlas.
- Sin cambios de requisitos, permisos, datos o dependencias. No se ejecutan tests. Compilación y sintaxis se revisan; presentación y funcionamiento quedan pendientes de revisión del usuario. Las etapas 4 a 7 se reservan para la siguiente entrega.

---

## Sprint 4: ajustes de navegación y reservas — 18/10/2026

- Se aclaran nombres y accesos entre reservas, solicitudes de insumos, inventario y préstamos entregados. Se incorpora ubicación con regreso al panel y al listado. Finanzas distingue Aportes de sede. Se conservan módulos y permisos.
- Presidente/secretario ven Reserva en ventanilla. El formulario no describe las actividades como gratuitas; se usa Sin aporte cuando corresponde.
- Al seleccionar una agrupación se completa su representante vinculado o el socio activo con el mismo RUT válido; sin ficha, se completan datos de ventanilla. Se añade prueba de coincidencia y rechazo por RUT.
- Sintaxis y compilación comprobadas; tests y revisión manual pendientes del usuario. Requisitos y despliegue sin cambios.

---

## Sprint 4: organización de pruebas generales y regresión — 18/10/2026

- A petición del usuario, la organización de las pruebas generales y su regresión se registran como parte del sprint 4, complementando el cierre funcional local ya aceptado. El usuario autorizó reorganizar las pruebas por módulos. Se movieron/renombraron 33 archivos y se conservaron los seis de Auth, manteniendo 39 archivos en Feature y el ejemplo existente en Unit.
- Los archivos que combinan módulos permanecen completos en Integracion; no se dividieron, fusionaron ni modificaron casos, preparación o funciones auxiliares. La revisión estática no encontró funciones auxiliares invocadas desde otros archivos ni dependencias de rutas relativas. Pest.php y phpunit.xml permanecen sin cambios porque su alcance ya incluye subcarpetas de Feature.
- Comparación SHA256 antes/después de cada movimiento: contenido idéntico. Sintaxis PHP revisada correctamente, sin ejecutar tests. El usuario confirmó posteriormente que ejecutó los tests y probó manualmente la aplicación tras la reorganización: todo funciona correctamente. Queda completada la validación de esta actividad del sprint 4; las ejecuciones corresponden al usuario.
- tests/README.md documenta carpetas, comandos y correspondencia de los nombres anteriores. Referencias actualizadas en documentación de sprints y revisión. No se modifica aplicación ni requisitos, no se instala nada ni se ejecutan migraciones o tests. Se mantiene el cierre local del sprint 4; evidencias, concurrencia real y despliegue siguen separados.

---

## Cierre local del sprint 4 — 18/10/2026

- El usuario confirmó: «Migracion, tests posteriores y revisión manual del aporte 0 funcionan bien» y solicitó registrar el cierre del sprint 4. Se registra como completado en entorno local, junto con las comprobaciones previas de los bloques y el recorrido integrado del sprint 3.
- Alcance de la última aceptación: migración aplicada, tests posteriores a los últimos ajustes y revisión manual del aporte $0. No se atribuyen ejecuciones al agente, cantidades de tests, capturas ni verificaciones adicionales no comunicadas.
- Desarrollo local previsto del MVP cerrado. Los hallazgos de la revisión de requisitos fueron resueltos con autorización; las reglas vigentes permanecen en REQUIREMENTS.md, sin nuevas modificaciones en este cierre.
- Pendientes separados: preparar evidencias de entrega cuando se solicite; comprobar concurrencia real sobre una base de prueba; y retomar el despliegue en Ubuntu/AWS en otro momento. El cierre local no acredita esos trabajos ni autoriza iniciarlos.
- Actualizados SPRINTS.md, docs/PRUEBAS_FINALES.md y docs/REVISION_REQUISITOS.md para reflejar la confirmación. No se modifica código, no se ejecutan tests ni migraciones y no se producen evidencias de demostración.

---

## Aportes voluntarios, otorgante y comprobantes — 18/10/2026

- Autorización del usuario para resolver los cinco hallazgos. Confirmación expresa: residencia admite aporte $0 sin comprobante. Se actualizaron la introducción y RFS-17, reglas de certificados/aportes/auditoría/respaldo y RFS-104, sin formular una conclusión jurídica: es la regla de negocio acordada para el MVP.
- Migración 000014 agrega nombre/cargo histórico de otorgante y ruta de comprobante en ingresos. Sin recalcular importes ni atribuir nombres actuales a emisiones anteriores. Nuevas emisiones conservan la identidad incluso tras renombrar, cambiar rol o eliminar la cuenta; PDF, detalle y verificación pública usan esa copia.
- Residencia en línea/ventanilla: importe sugerido desde Parámetros, editable y guardado en la operación. Con $0 no requiere respaldo ni verificación del aporte ni crea ingreso; sigue la revisión documental. Con aporte positivo conserva comprobación e ingreso automático del importe elegido, aunque cambie la sugerencia. Las solicitudes anteriores sin importe conservan la compatibilidad del flujo anterior al emitirse. Las descargas del certificado de socio siguen sin auditoría.
- Ingresos manuales admiten archivo privado PDF/JPG/PNG hasta 5 MB; transferencia acepta número o archivo. Descarga protegida para presidente/tesorero y respaldo conservado tras anular. Textos y conceptos visibles presentan aportes voluntarios destinados a la comunidad, manteniendo claves históricas y garantías/gastos separados.
- Preparado Integracion/AportesCertificadosYComprobantesTest.php para importes cero/positivos, revisión, monto conservado, identidad histórica, exclusión de auditoría y comprobantes/roles. No se ejecutan tests ni migraciones; no se instalan paquetes, producen evidencias de demostración ni se trabaja en despliegue.

---

## Revisión final de requisitos — 18/10/2026

- Autorización específica del usuario para conservar los accesos actuales y precisar RFS-81/83 y la tabla de permisos: administración de usuarios, roles, parámetros y catálogos exclusiva del administrador. No se modificó código ni otros requisitos.
- Revisión estática consolidada en docs/REVISION_REQUISITOS.md, cruzando RFU/RFS por bloques con rutas, modelos, controladores, migraciones, vistas y pruebas existentes. Hallazgos: textos antiguos de insumos sin ficha, falta de copia histórica de identidad del otorgante, precio de residencia configurable frente al monto fijo requerido, alcance de auditoría de descargas y significado de comprobante en ingresos manuales.
- Las pruebas existentes no se presentaron como nuevas ejecuciones. Queda por registrar el resultado posterior a las cuatro correcciones recientes; no se afirma que sigan fallando.
- El usuario pidió solo ideas de evidencia: se propusieron fichas de escenario, logs de la suite, capturas, videos y exportaciones ficticias, sin producir esas evidencias. No se ejecutaron tests ni se realizaron demostraciones. Despliegue aplazado expresamente para otro momento.

---

## Ajustes finales del sprint 4 autorizados

- El usuario confirmó que toda la suite funcionó y, tras la revisión manual, solicitó ajustes. Autorizó expresamente actualizar requisitos y confirmó las propuestas: dirigentes también socios con ficha activa para trámites personales, validación por otro presidente/secretario y ventanilla exclusiva para personas sin ficha. Se actualizaron RFS-39/50/71/82/92, RFU-67 y se incorporaron RFS-102/103. No se modifica la discrepancia RFS-81/83.
- Regularización mediante la inscripción existente, disponible también a dirigentes y con cita de firma. Aviso y enlace a la ficha personal; el cargo conserva sus permisos operativos. Bloqueo en servidor de resolución de la ficha propia, incluidos aprobación y activación, y controles correspondientes ocultos. Certificados de residencia ya se autorizaban por ficha; el tesorero activo dispone de solicitudes propias de sede.
- Reservas y arriendos comparten pantalla, formulario por pasos y bandeja. Se conservan rutas y registros históricos; las actividades gratuitas del catálogo reutilizan su validación y las solicitudes de sede admiten aporte cero o positivo. Las personas sin ficha se registran por ventanilla. Se retiró la vista de gestión sustituida. Calendario conserva su función de consulta separada.
- El comprobante en línea solo lo adjunta el titular, también protegido por servidor. En ventanilla la directiva puede registrar el respaldo recibido, identificado como tal. No se cambian montos, estados, ingresos ni garantías históricas automáticamente.
- Migración 000013: RUT opcional en base para históricos, obligatorio y validado en nuevas designaciones de representantes y solicitudes de insumos. Autocompletado desde la ficha, copia del RUT en la operación y comprobación al reservar/generar reservas de grupo. Los dirigentes sin ficha activa ya no solicitan insumos propios por la excepción anterior; el responsable operativo sigue seleccionándose por su cargo.
- Fechas: elección directa de mes/año y valor visible dd/mm/aaaa; separación de flecha/texto en selectores y ancho de agrupación/mes. Menú Reservas incluye sede e insumos; Mis certificados es directo. Finanzas identifica Préstamo de insumos; navegación de sede usa Reservas de sede. Identidad JJVV Digital sustituye el SVG de Laravel en las pantallas públicas de autenticación. Botón público de verificación destacado; filtro de proyectos retirado de la vista.
- Verificación estática completada: sintaxis PHP de controladores/modelo/migración/tests, compilación Blade y sintaxis de vistas afectadas, rutas de inscripción con sus roles y compilación de assets mediante npm run build. Diferencias sin errores de formato.
- Preparado Integracion/FichasDeDirigentesYSolicitudesTest.php y adaptados los casos anteriores afectados por las reglas nuevas. No se ejecutaron tests ni migraciones ni se instalaron paquetes. Requiere migración, comprobación de regresión por el usuario y revisión visual de fechas, selectores y navegación antes del cierre local. No se han realizado acciones sobre AWS.

---

## Inicio del sprint 4 y cierre integrado del sprint 3

- El usuario confirmó que `Integracion/CicloCompletoDePrestamoTest.php` terminó correctamente. Se registra el cierre integrado del sprint 3; esta confirmación no equivale a ejecutar toda la suite ni a comprobar Ubuntu o concurrencia real.
- Se preparó `docs/PRUEBAS_FINALES.md`: regresión conjunta mediante `php artisan test`, recorrido manual por roles y módulos, y comprobaciones de entorno separadas. La configuración existente usa SQLite en memoria para los tests.
- Se revisaron la configuración de pruebas, los tests base de permisos/autenticación/perfil y el comando programado de vencimientos. No se modificó lógica de aplicación, permisos ni REQUIREMENTS.md; no se ejecutaron tests, migraciones ni acciones sobre AWS.
- Sprint 4 permanece en curso, pendiente de los resultados de la suite completa y el recorrido manual final del usuario. Se conserva la discrepancia documental RFS-81/83 para resolver con autorización específica.

---

## Revisión de cierre del sprint 3

- El usuario confirmó los tests de Reportes y su presentación, y pidió continuar. Se revisaron los requisitos vigentes, rutas, controladores/modelos de Actas, Proyectos, Inventario, Solicitudes, Finanzas y Reportes, y la evidencia de los bloques anteriores. No se modificaron REQUIREMENTS.md ni lógica de aplicación; no se ejecutaron tests.

| Área | Requisitos revisados | Resultado |
| --- | --- | --- |
| Actas | RFS-73/74/75 | Registro de fecha/tipo/acuerdos/escaneo por presidente/secretario; consulta de asamblea para socios activos/dirigentes y directiva solo para dirigentes, con igual autorización del archivo privado. Implementación y pruebas del bloque confirmadas anteriormente. |
| Proyectos | RFS-76/77/78 | Registro/edición por presidente, consulta con sesión, selección de dirigente y conservación del nombre histórico sin reasignarlo al editar otros campos. Pruebas del bloque y corrección de sus datos de preparación confirmadas. |
| Catálogo y existencias | RFS-69/95/101 | Catálogo administrado solo por administrador, selección de tipos y edición de total por presidente/secretario, respetando unidades reservadas/prestadas/dañadas. Pruebas confirmadas. |
| Solicitudes y préstamos | RFS-70/71/72/96/98/99 | Elegibilidad, representación, varias líneas, responsable seleccionado, aprobación con reserva, entrega sin descuento doble, devolución completa con daños fuera de servicio y avisos por punto rojo. Pruebas y funcionamiento manual confirmados. |
| Cobros y garantías | RFS-58/59/60/62/63/64/97/100 | Montos por préstamo, recepción e ingreso vinculados, garantía separada, resolución completa/parcial, reingreso tras anulación e historial conservado. Comprobación financiera y regresión confirmadas. |
| Reportes | RFS-79/80 y aclaraciones de alcance | Finanzas por período/grupo con Sin agrupación para movimientos sin vínculo; socios exclusivamente activos sin contactos en XLSX/PDF; permisos diferenciados. Tests y presentación confirmados en el último mensaje del usuario. |

- No se identificó en esta revisión una funcionalidad nueva faltante del alcance implementado del sprint 3. Se mantiene documentada la discrepancia transversal RFS-81/83 sobre el acceso general del presidente frente a usuarios/roles exclusivos del administrador, sin modificar permisos ni requisitos silenciosamente.
- La evidencia anterior corresponde a comprobaciones por bloque. Para cerrar el recorrido conjunto se preparó `Integracion/CicloCompletoDePrestamoTest.php`, sin ejecutar: solicitud de un socio representante, aprobación por secretario con responsable tesorero, cobro de $6.000 y garantía de $10.000, entrega de tres unidades, devolución completa con una dañada, retención de $2.500 y devolución de $7.500, reporte por grupo, anulación y reingreso del cobro.
- El caso verifica disponibilidad de 5 a 2 al aprobar, sin otro descuento al entregar y 4 disponibles tras devolver con daño; garantía separada del saldo; neto del grupo $8.500, luego $2.500 al anular el cobro y nuevamente $8.500 al reingresar, sin repetir resolución ni cambiar stock. También comprueba separación de permisos operativos y financieros.
- Sintaxis del archivo revisada y diferencias sin errores de formato. No requiere migraciones ni dependencias nuevas. Queda pendiente la ejecución del usuario antes de registrar el cierre integrado; no se acredita una ejecución de toda la suite, concurrencia real ni funcionamiento del despliegue Ubuntu/AWS. Las comprobaciones finales corresponden al sprint 4.

---

## Reportes financieros y listado de socios activos

- Se continúa tras la comprobación de finanzas de insumos. El usuario aclaró que organización significa grupo/agrupación vecinal; los movimientos sin vínculo se muestran como Sin agrupación. El listado debe incluir solo socios activos y excluir datos de contacto. Se implementan estas elecciones sin modificar REQUIREMENTS.md.
- Módulo Reportes con acceso según rol y rutas de descarga protegidas: finanzas/PDF para presidente/tesorero; socios/Excel/PDF para presidente/secretario. Administrador y vecino no acceden, independientemente de tener ficha activa. En Socios se añadió acceso al listado exportable. Auditoría permanece al final del menú.
- Reporte financiero: período configurable, agrupación o Sin agrupación, pagos recibidos vigentes, egresos vigentes, cuotas devueltas y resultado neto del período. Usa fecha de pago para ingresos/egresos y fecha de devolución para cuotas; incluye ambos extremos del rango. No equivale al saldo acumulado ni reconstruye estados históricos anteriores a anulaciones posteriores.
- Los grupos se obtienen únicamente de vínculos guardados a reservas, solicitudes de insumos o garantías de esas operaciones. Egresos, cuotas y aportes manuales sin vínculo permanecen Sin agrupación aunque el concepto o un nombre mencione un grupo. No se crean asociaciones ni se modifica ningún movimiento.
- Las garantías recibidas/devueltas no se suman a ingresos/egresos; las retenciones vigentes ya están contadas como ingresos. Se muestran por separado solicitudes de sede y arriendos históricos según fecha de inicio y estado, con importe acordado sin sumarlo otra vez a pagos. Las pendientes con plazo vencido se identifican al mostrar sin guardar cambios de estado.
- La descarga PDF financiera utiliza la misma consulta y filtros que la vista. Moneda CLP, fechas dd/mm/yyyy, identificación de agrupación y fecha de generación, tablas con encabezados repetidos, formato A4 horizontal y fuentes locales. La composición visual final queda para la comprobación del usuario.
- Listado solo de socios activos, búsqueda opcional por nombre/RUT y vista paginada. PDF y Excel incluyen todos los resultados filtrados, sin limitarse a la página visible. Únicamente nombre, RUT, estado y fecha de ingreso; no se consultan ni exportan dirección, teléfono o correo. Las fechas faltantes no se sustituyen por otras fechas supuestas.
- Excel es un archivo XLSX real creado con ZipArchive y XML, sin paquetes nuevos: RUT/valores tratados como texto, nombres escapados y sin fórmulas ejecutables. Archivo temporal privado eliminado tras enviarlo, con caché privada y sin almacenamiento público. ZIP está disponible en PHP local y debe estar habilitado también en Ubuntu para esta exportación. PDF reutiliza Dompdf ya instalado.
- Preparado `Reportes/ExportacionesYFiltrosTest.php`: límites del período, exclusión de anulados/fuera de rango, neto con devoluciones, agrupación de pagos/retenciones, ausencia de vínculos inventados, validación, socios activos sin contactos, búsqueda y exportación de más de una página, estructura XML/XLSX y texto sin fórmulas, descargas PDF, autenticación y permisos.
- Verificación estática: sintaxis PHP, seis rutas con sus roles, compilación Blade con lint de vistas afectadas y assets compilados. No se ejecutaron tests ni migraciones ni se instalaron dependencias. Pendiente ejecutar el archivo de tests y comprobar visualmente los PDF y el Excel en el entorno del usuario; no se declara cerrado el sprint 3 sin esa confirmación y el recorrido final.

---

## Integración financiera de préstamos de insumos

- Confirmación posterior a la entrega: el usuario ejecutó los tests indicados y confirmó que todo funciona correctamente. Queda registrada la comprobación del bloque nuevo y de la regresión indicada, sin atribuir una ejecución al agente ni pruebas de concurrencia real. El próximo bloque es Reportes.

- El usuario confirmó tests y funcionamiento manual de la entrega anterior de solicitudes/préstamos y autorizó continuar. Se registró esa evidencia en el resumen. No se modificó REQUIREMENTS.md, no se ejecutaron tests ni migraciones y no se instalaron dependencias.
- Migración `2026_10_20_000012_add_finanzas_to_solicitudes_insumos.php`: metadatos de recepción/comprobante en solicitudes, vínculo desde ingresos y vínculo opcional desde garantías; reserva_id de garantía admite null para el origen préstamo. Conserva las garantías/ingresos anteriores y sus identificadores, montos y resoluciones. El rollback se rechaza si ya existen registros financieros nuevos, para evitar perder trazabilidad.
- Finanzas > Préstamos y garantías, exclusivo de presidente/tesorero: consulta de aprobados/entregados/devueltos, sin ampliar permisos operativos del tesorero sobre inventario ni solicitudes ajenas. El detalle financiero tiene regreso a su bandeja y el presidente puede abrir la gestión operativa.
- Registro conjunto de recepción por el cobro y la garantía acordados en el préstamo; los montos y el autor se toman del servidor. Cobro positivo genera ingreso con concepto propio prestamo_insumos, sin clasificación ordinaria/extraordinaria. Garantía positiva crea registro separado; cobro $0 no crea ingreso y garantía $0 no crea garantía. Si ambos son cero no se registra recepción ficticia.
- Efectivo requiere talonario; transferencia requiere archivo o número de transacción. Archivo opcional PDF/JPG/PNG hasta 5 MB, privado y accesible por rutas financieras. Fecha entre solicitud y hoy. La recepción original conserva fecha, medio, respaldo y autor.
- Escrituras financieras comparten la serialización de garantías existentes y bloquean/recargan la solicitud; recepción repetida no duplica ingreso ni garantía. Tras anular el cobro, una acción específica vuelve a registrarlo con los datos originales sin recibir nuevamente la garantía. El concepto vinculado no puede crearse desde el formulario genérico.
- Tras devolución completa de los insumos, la garantía recibida pasa a por devolver; si se registra financieramente después de la devolución se crea en ese estado. Su resolución exige primero esa devolución y una fecha no anterior a ella ni al pago. La devolución total no afecta otra vez al saldo; la retención manual parcial/total genera ingreso solo por lo retenido y guarda el resto devuelto, motivo, fecha y autor.
- La resolución usa las rutas financieras existentes con comprobaciones específicas para insumos. Las garantías históricas de sede conservan su comportamiento. Las vistas de resolución se comparten entre ambos orígenes para no duplicar sus formularios. Los enlaces de ingresos/retenciones distinguen préstamo frente a sede y llevan a su detalle y respaldo correspondientes.
- Una retención anulada puede reingresarse sin repetir la resolución ni la devolución del resto. Historial y auditoría usan los modelos financieros existentes; la categoría Finanzas incluye ambos orígenes. No se crean egresos ni devoluciones de cuotas para resolver una garantía.
- Preparado `Finanzas/AportesYGarantiasDeInsumosTest.php`: montos/autoría, registro único, operaciones gratuitas/con o sin garantía, efectivo/transferencia/archivos/fechas, privacidad, reingreso tras anulación, devolución previa, resolución total/parcial, límites y auditoría sin cambios tras rechazos. Ampliado el test de ingreso genérico en `Finanzas/ConsistenciaDelSaldoTest.php` para comprobar que no acepta un vínculo de préstamo enviado por el cliente.
- Verificación: sintaxis PHP, middleware financiero, Blade compilado con lint de vistas y assets compilados. No acredita ejecución de tests, simultaneidad real ni migración de una base histórica. El usuario ejecutará el archivo nuevo y la regresión de solicitudes, garantías históricas y cierre financiero.
- Comprobación manual sugerida: aprobar préstamo con cobro/garantía; registrar recepción con tesorero y comprobar saldo solo por cobro; devolver insumos con secretario, resolver garantía con tesorero y comprobar importes; anular/reingresar cobro o retención; probar préstamo gratuito con garantía y otro sin garantía, respaldo privado y regreso contextual. Reportes permanece pendiente.

---

## Entrega posterior al resumen — solicitudes y préstamos de insumos

- Implementación autorizada al continuar con el sprint 3. Se conservan requisitos; no se ejecutaron tests ni migraciones y no se instalaron dependencias. Migración `2026_10_20_000011_create_solicitudes_insumos.php`: solicitudes con datos históricos del solicitante/grupo, responsable, autores y fechas; líneas de insumos; vínculo opcional desde préstamos. Las filas anteriores no se reasignan ni recalculan.
- Solicitudes propias de socios activos y dirigentes, incluso sin ficha activa; ventanilla para socios/dirigentes o representantes externos seleccionados desde grupos vigentes. No se admiten nombres libres. Si existe una sola agrupación representada se completa; si existen varias se exige elegir una propia. La etiqueta de representante no amplía permisos. Contacto sugerido, varios insumos del catálogo sin repeticiones y fecha comprometida. Recordatorio de atención sin generar citas.
- Presidente/secretario aprueban todas las líneas tras comprobar existencias. Selección obligatoria de dirigente responsable; nombre histórico separado del solicitante y registrador. Monto y garantía por el conjunto, incluyendo cero. Se vuelve a comprobar elegibilidad y representación antes de aprobar. La reserva solo comienza al aprobar, no al solicitar.
- Las transiciones bloquean la solicitud y los ítems en orden estable; la edición de cantidad y devoluciones históricas comparten el bloqueo del ítem. Disponibles descuenta reservas aprobadas, entregas abiertas y daños previos. La entrega crea las líneas de préstamo y cambia el estado en la misma transacción, evitando doble ocupación. Las operaciones repetidas o en estado incorrecto se rechazan.
- Entrega completa con fecha y estado por insumo. Devolución completa con fecha efectiva, condición y daños de todas las líneas; primero valida el conjunto y luego registra. Solo las utilizables vuelven a estar disponibles. Se bloquea devolver una línea nueva desde la ruta histórica para impedir devoluciones parciales.
- Solicitudes pendientes/aprobadas en Solicitudes; entregados/devueltos en Préstamos. Consulta propia de socios/dirigentes; gestión solo presidente/secretario, sin ampliar al tesorero acceso al inventario ni a solicitudes ajenas. Históricos en sección propia, con detalle y devolución conservados; las altas antiguas redirigen al flujo nuevo y rechazan nuevos préstamos libres.
- Puntos rojos sin contadores para presidente/secretario: solicitudes pendientes en Solicitudes e insumos entregados con fecha comprometida anterior a hoy en Reservas. No se incorporan correos, lectura ni cambios de fechas supuestos en históricos.
- Preparado `Inventario/SolicitudesDeInsumosTest.php` para elegibilidad, grupos, privacidad, autoría, catálogo, validación, aprobación atómica del conjunto, stock reservado, edición de cantidad, entrega/devolución, reintentos, daños, fechas, bandejas y avisos. Adaptado `Inventario/ExistenciasYPrestamosHistoricosTest.php` para preparar históricos por el modelo y conservar sus pruebas de devolución; altas nuevas se cubren en el archivo nuevo. `Inventario/CatalogoYExistenciasTest.php` se conserva.
- Verificación estática: sintaxis PHP, middleware de rutas, compilación Blade con lint de vistas afectadas, assets compilados y diferencias revisadas. No acredita tests, concurrencia real, migración sobre una base histórica ni comprobación visual. Pendiente validación del usuario.
- Comprobación manual sugerida: solicitar como socio y tesorero sin ficha, registrar representante externo en ventanilla, aprobar dos insumos, intentar sobreasignar o reducir total reservado, entregar, devolver con daños y revisar disponibilidad; comprobar privacidad, puntos rojos, bandejas y devolución histórica.
- Pendiente siguiente entrega: registrar cobro y garantía en Finanzas, comprobantes, resolución y retención de la garantía. En esta entrega solo se guardan importes acordados; no se crean ingresos ni garantías financieras ni se implementan reportes.

---

Archivo histórico. El estado vigente se resume en [SPRINTS.md](../SPRINTS.md).
Las notas siguientes conservan íntegro el documento anterior; sus estados y propuestas corresponden al momento en que se registraron.

---

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

### [REVISIÓN DE CIERRE DOCUMENTADA; AMPLIACIÓN DE INSUMOS PENDIENTE] Sprint 2: SHOULD HAVE

Entregables:

- Grupos y organizaciones, con su representante vigente.
- Calendario y reservas: consulta de ocupación, gestión separada de reservas gratuitas o con aporte variable, horarios fijos de grupos y horario imprimible. Adaptación al aporte por operación implementada y comprobada por el usuario, según la ampliación registrada al final.
- Módulo financiero: ingresos (con cuotas ordinarias y extraordinarias), egresos, devoluciones, anulaciones y saldo de caja. La ampliación contempla garantías de insumos y conservación de garantías históricas de sede; la versión previa de arriendos permanece documentada más adelante.
- Auditoría de finanzas y consulta de auditoría con filtros por fecha, autor y tipo de registro.

### [EN CURSO] Sprint 3: COULD HAVE

Entregables:

- Actas de asamblea y de directiva, con acceso según rol.
- Proyectos postulados y adjudicados, visibles para todo usuario con sesión iniciada.
- Inventario y préstamos de insumos.
- Reportes: financieros y listado de socios en Excel y PDF.

#### [Incompleto] Sprint 4: TESTING & BUFFER

Objetivo: Pruebas, corrección de errores (bugs).

## Avance actual

El 17-10-2026 se aprobó un nuevo ciclo de mejoras de interfaz y reglas de negocio. El usuario autorizó expresamente la actualización puntual de REQUIREMENTS.md con las reglas acordadas. Los estados y la revisión del 16-10-2026 que siguen describen la versión anterior; no certifican que las nuevas reglas ya estén implementadas. Los cinco bloques de mejoras tienen implementación; su evidencia de comprobación se detalla al final de este documento. Tras la indicación del usuario de continuar, se inició el sprint 3 por Actas.

Se acordó después una ampliación de navegación, reservas e insumos, cuya actualización puntual de REQUIREMENTS.md fue autorizada expresamente por el usuario («autorizada»). Su alcance y entregas pendientes se registran al final. Las confirmaciones anteriores corresponden a las versiones previas y no acreditan la implementación ni las pruebas de estas reglas nuevas.

Estado vigente del cierre del sprint 2: el alcance original tiene implementación y revisión documentada. El usuario confirmó las pruebas de recurrencias/calendario, cierre financiero y auditoría, además de las reservas con aporte variable y las garantías históricas. Queda pendiente la ampliación financiera de préstamos de insumos, dependiente del flujo nuevo del sprint 3. La matriz vigente y los límites de comprobación se encuentran al final de este documento; no se acredita una ejecución conjunta de toda la suite ni concurrencia real.

Las secciones de hallazgos, bloques originales y revisión del 16-10-2026 que siguen son un registro histórico. Sus menciones a comprobaciones pendientes, tarifas fijas o retiro del representante por baja deben leerse con las correcciones posteriores y la matriz vigente, sin sustituir REQUIREMENTS.md.

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
- Archivo `tests/Feature/Reservas/SolicitudesYFinanzasDeSedeTest.php`: solicitudes, representación, comprobante privado, superposición, vencimiento, confirmación, cancelación, permisos, registro financiero, devoluciones, retención parcial/total, auditoría y anulación/reingreso. Ejecución satisfactoria confirmada por el usuario. Los envíos repetidos se comprueban secuencialmente; no se simula concurrencia real.

### Bloque 4 [Completado; funcionamiento del horario imprimible confirmado por el usuario]:

trazabilidad financiera (RFU-60, RFS-85 y RFS-87) y horario imprimible (RFU-38, RFS-57). La consulta de auditoría financiera incluye ingresos, egresos, devoluciones y garantías.

- Presidente y secretario pueden elegir una fecha desde calendario e imprimir la semana o el mes correspondiente.
- Vista por día con fecha, horario, actividad y estado; incluye reservas vigentes y horarios fijos aún sin generar. Excluye reservas canceladas/vencidas y respeta las ocurrencias fijas canceladas.
- Formato A4, encabezados de tabla repetidos y controles ocultos al imprimir; permite guardar PDF con el navegador, sin nuevas dependencias.
- El usuario confirmó que el horario imprimible funciona correctamente. No se han ejecutado tests adicionales por parte del agente.

## Próximos pasos de la revisión original — registro histórico

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

## Mejoras acordadas — 17-10-2026

Se trabaja por entregas. El usuario confirmó el funcionamiento de los bloques 1, 2 y 3 del plan aprobado. La tercera entrega implementa el bloque 4. El agente no ha ejecutado tests.

| Bloque | Estado | Alcance |
| --- | --- | --- |
| 1. Requisitos | Actualizados con autorización explícita | Representante sin ficha/cuenta y conservado tras baja; parámetros exclusivos del administrador; catálogo de actividades gratuitas; certificado de socio autónomo o de ventanilla solo para imprimir; horarios públicos recurrentes; arriendos de no socios solo en ventanilla. Se incorporaron RFU-65/66 y RFS-90/91, manteniendo los identificadores anteriores. |
| 2. Formularios, idioma y presentación | Funcionamiento confirmado por el usuario | RUT con dígito verificador al salir y antes de avanzar; lista de grupos retráctil con altura acotada; editar grupo y solicitar arriendo desplegables que se abren con errores; resumen final de asistentes sin contraseñas; botones de archivo en español; Editar datos dentro de Cuenta de usuario; selector de impresión con espacio suficiente. |
| 3. Representantes, parámetros y actividades | Funcionamiento confirmado por el usuario | Representante con datos propios, autocompletado desde socios/dirigentes o manual, sin ficha/cuenta obligatoria; se conserva tras baja o eliminación de cuenta y puede retirarse explícitamente. Parámetros y catálogo protegidos por rol administrador. Actividades gratuitas creables, renombrables y desactivables para nuevas reservas manuales. Reservas guardan nombre del tipo y responsable para conservar el historial. |
| 4. Certificados y arriendos | Implementado; pendiente de migración y comprobación manual | Certificado de socio autónomo emitido de inmediato y sin pago; certificado de socio emitido por directiva solo para imprimir; residencia mantiene pago/revisión y descarga. Arriendos de no socios en ventanilla con los mismos pagos, garantía y registro financiero. |
| 5. Atención, panel y navegación | Implementado; pendiente de migración y comprobación manual | Horarios públicos semanales con varios tramos; recordatorio para habilitar citas al aprobar documentos; socios y certificados pendientes en panel del presidente; puntos rojos sin contadores para presidente/secretario; Finanzas agrupa ingresos, egresos y arriendos/garantías. Regreso contextual del detalle conservado. |

Idioma de aplicación y fallback fijados en español, zona horaria America/Santiago. Los controles de fecha usan un calendario en español y muestran dd/mm/aaaa; envían yyyy-mm-dd al servidor. La selección del mes de generación ofrece los siete meses permitidos con nombres en español. Las fechas numéricas visibles usan barras. La primera entrega (bloques 1/2) no requiere nuevas dependencias ni migraciones.

Comprobación manual sugerida: inscripción con RUT incorrecto/correcto; navegación completa hasta Resumen y regreso para editar; carga de archivos; fechas escritas y elegidas, límites y campos condicionales; apertura/cierre de grupos y arriendos y reapertura ante errores; perfil y vista imprimible en español. Al desplegar en Ubuntu, publicar los assets compilados y limpiar la caché de configuración/vistas para aplicar idioma y controles nuevos.

Se mantiene la regla de AGENTS.md: la autorización de requisitos concedida en esta entrega no autoriza modificaciones futuras de REQUIREMENTS.md.

### Entrega del bloque 3

- Nueva migración `2026_10_20_000001_add_representantes_libres_and_tipos_actividad.php`: copia los datos de representantes vinculados, agrega los datos históricos de responsables/tipos a reservas y crea el catálogo con los tres tipos gratuitos originales. No elimina grupos, reservas ni fichas.
- Aplicar `php artisan migrate` antes de abrir grupos, calendario o parámetros. No se ejecutó la migración sobre la base del usuario.
- Selección de una persona solo autocompleta: no crea usuarios ni socios. Un nombre modificado se conserva como representante manual, sin vincularlo erróneamente a otra ficha. El contacto del grupo se puede copiar o editar por separado.
- Un representante manual puede recibir reservas gratuitas y horarios fijos. La ampliación de arriendos en ventanilla a personas sin ficha se implementó después, en la entrega del bloque 4.
- Desactivar tipos no altera las reservas guardadas ni la programación fija ya existente. Cambiar su nombre se aplica a las reservas nuevas; las anteriores conservan el nombre original.
- Actualizados los tests de parámetros, permisos y baja de representantes para reflejar los requisitos nuevos. Agregado `Integracion/RepresentantesYCatalogoDeActividadesTest.php` con casos de representante manual/dirigente, conservación de historial y permisos del catálogo. No se ejecutaron tests.
- Comprobación manual sugerida: crear grupo con representante manual; crear otro autocompletando un dirigente; editar contacto; dar de baja a un socio representante y comprobar que el grupo lo conserva; reservar y generar horarios; retirar representante y verificar bloqueo de reservas nuevas; probar acceso a parámetros con admin/presidente/secretario; crear, renombrar y desactivar una actividad y comprobar el historial.

### Entrega del bloque 4

- Nueva migración `2026_10_20_000002_add_certificados_impresion_and_arriendos_ventanilla.php`: agrega la marca de impresión y los datos/canal del arrendatario. Copia datos de las fichas vinculadas a arriendos existentes. No cambia el acceso de certificados ya emitidos ni asigna un canal supuesto a los arriendos antiguos.
- Aplicar `php artisan migrate` antes de usar certificados o arriendos. La migración no fue ejecutada por el agente.
- Certificado de socio: emisión inmediata desde Mis certificados con datos de la ficha activa y destinatario. No requiere comprobante ni crea ingreso. No queda sujeto al límite de solicitudes pendientes. Se usa al único usuario con rol presidente como otorgante; si falta o hay varios, se informa que administración debe resolver esa asignación antes de emitir.
- Creación y emisión autónoma en una transacción; emisión directa y autónoma comparten un bloqueo para asignar folios. Auditoría conserva como autor al usuario que ejecuta la operación.
- Nuevos certificados de socio emitidos por presidente/secretario desde la gestión quedan solo para impresión. La marca se comprueba en listado y en autorización de descarga, incluso si se intenta la URL directamente o existe un vínculo a la ficha. Residencia conserva sus pagos, revisión y disponibilidad para el titular.
- Ventanilla: presidente/secretario elige socio activo o ingresa nombre, RUT válido, domicilio y teléfono sin crear ficha/cuenta. Los grupos admiten representantes manuales; el responsable debe coincidir con el representante. En línea se conserva la exigencia de socio activo y la identidad se toma del servidor, no de campos manipulados.
- Se mantienen comprobante único por el total, montos guardados al solicitar, bloqueo de horario, vencimiento, validación de pago/garantía, registro financiero posterior y resolución separada de garantía.
- El detalle muestra los datos guardados del arrendatario. Finanzas admite los registros sin socio vinculado. Un RUT coincidente no otorga acceso a un arriendo de ventanilla.
- Regreso contextual adelantado del bloque 5: desde solicitudes vuelve a Arriendos; desde Finanzas vuelve a arriendos y garantías. El contexto usa un valor permitido, no una URL libre.
- Agregado `Integracion/CertificadosYReservasDeVentanillaTest.php` con casos de emisión autónoma, impresión privada, residencia, ventanilla sin ficha, representantes manuales, validación y permisos. Tests preparados, no ejecutados.
- Comprobación manual sugerida: emitir certificado propio y abrir PDF; emitir uno de socio en ventanilla y verificar que no se ve ni descarga desde la cuenta del socio; confirmar que residencia sigue requiriendo pago/revisión; registrar arriendo manual particular y de grupo, confirmar y registrar el ingreso; comprobar privacidad y ambos enlaces de regreso.

### Entrega del bloque 5

- Nueva migración `2026_10_20_000003_create_horarios_atencion_table.php`. Aplicar `php artisan migrate` antes de abrir la portada o Atención al público. El agente no ejecutó migraciones ni tests.
- Presidente y secretario pueden publicar, editar y retirar tramos semanales desde Atención al público. Se permiten varios tramos por día y tramos contiguos, sin superposiciones entre ellos. La portada muestra la semana en español sin exigir cuenta; si no hay horarios, lo informa sin inventar disponibilidad.
- Horarios públicos independientes de citas y reservas: no generan ni restringen ninguno de esos registros. Aprobar la documentación de un vecino muestra un recordatorio con enlace a Agenda de firmas, sin crear citas automáticamente.
- Panel del presidente: hasta cinco solicitudes más antiguas de socios pendientes de revisión y certificados solicitados, con acceso a cada ficha y a la lista completa filtrada. No incluye vecinos pendientes de firma.
- Menú lateral de presidente/secretario: solo un punto rojo por apartado cuando hay socios pendientes de revisión, certificados solicitados o arriendos pendientes aún vigentes. Se recalcula al cargar cada página, sin contadores ni estado de lectura. Por firmar y arriendos vencidos no encienden el punto.
- Finanzas reúne Ingresos, Egresos y Arriendos y garantías en un desplegable, abierto al navegar por sus pantallas. La gestión operativa se identifica como Solicitudes de arriendo (Mis arriendos para socios). Se conservan los permisos: finanzas para presidente/tesorero, solicitudes para presidente/secretario y cuenta activa propia. El detalle distingue el contexto financiero al marcar el menú activo y al volver.
- Agregado `Integracion/AtencionYNotificacionesTest.php`: publicación pública, varios tramos, edición/retiro, validación y superposición, permisos, independencia de citas, recordatorio de aprobación, puntos rojos y privacidad del panel. Tests preparados, no ejecutados; no se acredita concurrencia real.
- Comprobación manual sugerida: publicar dos tramos el mismo día y revisar la portada sin sesión; editar y retirar uno; intentar superponer; aprobar documentos y usar el enlace del recordatorio; crear citas fuera de la atención pública; revisar pendientes y puntos rojos con presidente/secretario y confirmar que desaparecen al resolverlos; recorrer el desplegable Finanzas y ambos regresos del detalle de arriendo.
- No se modificó REQUIREMENTS.md en este bloque ni se inició el sprint 3. Queda pendiente la comprobación del usuario antes de declarar validado este cierre de mejoras.
- Verificación técnica del bloque 5: sintaxis PHP de los archivos nuevos/modificados, compilación de Blade y sintaxis de las vistas compiladas, rutas de atención con middleware de presidente/secretario, compilación de assets y revisión de diferencias sin errores. No reemplaza la comprobación funcional; tests no ejecutados.

## Sprint 3 — Bloque 1: Actas

Estado: completado; funcionamiento manual y tests satisfactorios confirmados por el usuario («funcionamiento ok, los tests ok»). Se inició tras la nueva indicación de continuar, posterior al cierre de implementación de las mejoras. No se modificó REQUIREMENTS.md; el agente no ejecutó tests.

- RFU-51 y RFS-73: presidente o secretario registra fecha, tipo (asamblea/directiva), acuerdos en texto y escaneo opcional. Autor completado desde la sesión. Formulario por pasos con fecha en español y resumen final. Se implementan registro y consulta; estos requisitos no solicitan edición ni eliminación de actas.
- Escaneos PDF, JPG o PNG de hasta 5 MB en almacenamiento privado. El acceso al archivo comprueba los mismos permisos que el detalle y se entrega sin caché compartida. No requiere enlace público de storage ni nuevas dependencias.
- RFU-52/53 y RFS-74/75: presidente, secretario y tesorero consultan ambos tipos. Una cuenta con ficha activa consulta únicamente asamblea, independientemente de su rol, conforme a RFS-82. El administrador no tiene acceso a directiva; sin ficha activa tampoco tiene acceso a asamblea. Vecinos sin ficha o con estado no activo no consultan actas.
- Listado paginado por fecha de reunión descendente, con filtro de tipo para dirigentes, detalle de acuerdos y enlace al escaneo. Actas aparece en el menú solo para quienes pueden consultar. Los permisos se comprueban también por URL y no dependen de ocultar enlaces.
- Nueva migración `2026_10_20_000004_create_actas_table.php`. Aplicar `php artisan migrate` antes de entrar a Actas. El agente no ejecutó la migración sobre la base del usuario.
- Archivo `tests/Feature/Actas/RegistroYConsultaTest.php` para registro con/sin escaneo, autor automático, privacidad, permisos por rol/estado, baja de socio, filtrado, campos/archivos inválidos y texto escapado. Matriz general de permisos ampliada con Actas. Ejecución satisfactoria de tests confirmada por el usuario; el agente no ejecutó tests.
- Verificación técnica: sintaxis PHP correcta; cinco rutas registradas y registro protegido por rol; Blade compilado y sus tres vistas nuevas revisadas con lint; assets compilados y diferencias sin errores de formato. Estas comprobaciones no acreditan funcionamiento manual ni ejecución de tests.
- Comprobación manual sugerida: registrar un acta de cada tipo con presidente/secretario; abrir el escaneo; consultar ambas con tesorero; comprobar que un socio activo solo ve asamblea y que la URL de directiva se rechaza; dar de baja al socio y volver a intentar detalle y archivo; comprobar que una cuenta sin ficha no obtiene acceso.
- Siguiente bloque propuesto: Proyectos (RFU-54/55 y RFS-76 a 78). Inventario y reportes continúan pendientes.

## Sprint 3 — Bloque 2: Proyectos

Estado: completado; funcionamiento manual y tests satisfactorios confirmados por el usuario («tests ok, todo funciona bien»). Se continúa después de la confirmación de funcionamiento y tests de Actas. No se modificó REQUIREMENTS.md ni se ejecutaron tests por parte del agente.

- RFU-54 y RFS-76: el presidente registra y edita título, organismo, fecha de postulación, estado (postulado, adjudicado o rechazado) y monto opcional en pesos enteros no negativos. Un monto sin indicar se conserva como null y se distingue de $0.
- RFS-77: nombre de quien ingresó la postulación en la plataforma del organismo, con sugerencias de dirigentes y nombres usados antes, admitiendo personas sin cuenta. El usuario que registra el proyecto en JJVV Digital se guarda automáticamente desde la sesión y se conserva al editar. Las sugerencias no obligan a vincular una cuenta ni suponen quién realizó la postulación externa.
- RFU-55 y RFS-78: listado y detalle disponibles para todo usuario con sesión iniciada, incluyendo administrador y vecinos sin ficha o con ficha no activa. Solo el presidente puede abrir el formulario de creación/edición y guardar cambios; las rutas comprueban el permiso aunque se manipule la URL. Se registran postulaciones externas; el módulo no envía postulaciones a organismos.
- Formulario compartido de creación/edición por pasos, resumen final, fecha en español y sugerencias de organismos anteriores. Listado paginado por fecha descendente con filtro por estado. Proyectos visible en el menú de todas las cuentas autenticadas.
- Nueva migración `2026_10_20_000005_create_proyectos_table.php`. Aplicar `php artisan migrate` antes de entrar al módulo. El agente no ejecutó la migración sobre la base del usuario. No se agregaron dependencias.
- Archivo `tests/Feature/Proyectos/RegistroYConsultaTest.php`: registro/edición, autor automático conservado, monto opcional/cero, consulta por rol y estado de ficha, permisos de escritura, validaciones, filtro/paginación y texto escapado. Matriz general de permisos ampliada con Proyectos. Ejecución satisfactoria de tests confirmada por el usuario; el agente no ejecutó tests.
- Verificación técnica: sintaxis PHP correcta, seis rutas registradas con escritura exclusiva del presidente, Blade compilado y sus tres vistas nuevas revisadas con lint, assets compilados y diferencias sin errores de formato. La comprobación funcional y la ejecución de los tests de este bloque quedan a cargo del usuario.
- Comprobación manual sugerida: registrar con presidente, seleccionar sugerencias o indicar otro postulador, revisar resumen, guardar sin monto, editar a adjudicado con monto, comprobar que se conserva el registrador; consultar con secretario, tesorero, administrador y vecino sin ficha; intentar editar con esos roles y verificar rechazo; filtrar por los tres estados.
- Siguiente bloque propuesto: Inventario y préstamos (RFU-48 a 50 y RFS-69 a 72). Reportes continúa pendiente.

## Sprint 3 — Bloque 3: Inventario y préstamos

Estado: implementado; pendiente de migración y comprobación funcional del usuario. El usuario aclaró devolución completa de todas las unidades y permanencia de las dañadas fuera de servicio. No se modificó REQUIREMENTS.md; el agente no ejecutó tests.

- RFU-48 y RFS-69: presidente y secretario registran nombre y cantidad total no negativa de cada ítem. Autor guardado automáticamente desde la sesión; acceso protegido por rol, también en las rutas. Lista paginada y formulario desplegable que se abre ante errores.
- Migraciones `2026_10_20_000006_create_items_inventario_table.php` y `2026_10_20_000007_create_prestamos_table.php`. Aplicar `php artisan migrate` antes de usar Inventario y préstamos, incluso si ya se aplicó la primera. No fueron ejecutadas por el agente. No se agregaron dependencias.
- RFU-49 y RFS-70/71: préstamos con ítem, cantidad, fechas de solicitud/entrega, estado de salida, responsable y teléfono; a grupo registrado o nombre libre. El grupo no necesita representante ni el responsable una ficha/cuenta. Se sugiere contacto del grupo o su representante, editable; el nombre del grupo queda guardado para conservar el historial tras renombrarlo. Se registran entregas efectuadas: solicitud no posterior a entrega y entrega no futura.
- Estados físicos seleccionables: buen estado, con desgaste o con daños. Formulario de préstamo por pasos con resumen, fechas en español y sugerencias de destinatarios anteriores.
- RFS-72: disponibles = total menos unidades de préstamos abiertos menos unidades devueltas dañadas. El total no se modifica al prestar/devolver. La disponibilidad se comprueba en servidor dentro de una transacción con bloqueo de escritura del ítem, compartido por préstamo y devolución y compatible con SQLite. Las cifras mostradas al abrir el formulario no sustituyen esa comprobación.
- RFU-50: devolución completa, fecha no anterior a entrega ni futura, estado de regreso y autor automático. Si hay daños, se exige cantidad entera entre 1 y las unidades prestadas; en los demás estados no se admite una cantidad dañada positiva. Cierra todo el préstamo, devuelve a disponibles solo las unidades utilizables y mantiene el resto fuera de servicio. Se impide repetir una devolución. No se incorporan devoluciones parciales ni un flujo de reparación/reincorporación.
- Inventario muestra total, en préstamo, fuera de servicio y disponibles. Listado de préstamos con filtro en préstamo/devuelto y detalle con responsable, fechas, condiciones y devolución. Todas las rutas restringidas a presidente y secretario.
- Ampliado `tests/Feature/Inventario/ExistenciasYPrestamosHistoricosTest.php`: registro, préstamos libres/de grupo, historial, saldo de unidades, préstamo por encima de disponibilidad, devoluciones completas sanas/con daños/todo dañado, repetición de devolución, validaciones y permisos. Tests preparados, no ejecutados; no se simula concurrencia real.
- Verificación técnica: sintaxis PHP y JavaScript correcta, siete rutas de inventario/préstamos con middleware de presidente/secretario, Blade compilado y sus cuatro vistas revisadas con lint, assets compilados y diferencias sin errores de formato. Pendiente la comprobación funcional del usuario.
- Comprobación manual sugerida: registrar cinco sillas, prestar tres, comprobar dos disponibles e intentar prestar tres más; devolver las tres indicando una dañada y comprobar cuatro disponibles y una fuera de servicio; intentar repetir la devolución; probar un grupo sin representante y un nombre libre; revisar fechas, resúmenes, filtros y accesos de presidente/secretario frente a tesorero/admin/vecino.
- Siguiente bloque propuesto: Reportes (RFU-56/57 y RFS-79/80).

## Ampliación autorizada — navegación, reservas e insumos

Estado inicial: requisitos actualizados con autorización explícita del usuario («autorizada»), posterior a la revisión del plan de cinco entregas y a las aclaraciones sobre garantías, solicitudes y disponibilidad. Esa actualización inicial solo modificó documentos. El avance posterior de implementación se registra en las entregas siguientes.

Se mantienen los identificadores anteriores, ajustando únicamente las reglas afectadas. Se incorpora RFU-67 y se agregan RFS-92 a RFS-101. La autorización es puntual: cualquier modificación futura de REQUIREMENTS.md requerirá una confirmación nueva para ese cambio.

| Entrega | Estado | Alcance y comprobación prevista |
| --- | --- | --- |
| 1. Interfaz y navegación | Implementado; pendiente de migración y comprobación del usuario | Contraseña junto al campo; desplegables y Auditoría al final; gestión de Reservas separada del Calendario con acceso a insumos; selector de dirigentes en Proyectos y etiqueta de representante. La nueva bandeja de solicitudes de insumos se conectará en la entrega 4, cuando exista su flujo, sin sustituirla por préstamos ya entregados. |
| 2. Catálogo e inventario | Funcionamiento y tests confirmados por el usuario | Catálogo inicial de 15 insumos en cuatro categorías, ampliable por administrador desde Parámetros; selección al registrar existencias y cantidad editable por presidente/secretario. Comprobación de mínimo según préstamos abiertos y unidades dañadas, con bloqueo compartido e historial conservado. La reserva previa a entrega se integrará con el flujo de la entrega 4. |
| 3. Reservas de sede | Funcionamiento y tests confirmados por el usuario | Aporte manual por operación definido por presidente/secretario; $0 gratuito; sin tarifas fijas ni garantía para reservas nuevas. Solicitud en línea, definición de aporte y comprobante posterior, confirmación y registro financiero vinculado. Se conservan pagos y garantías históricos. Tests ejecutados satisfactoriamente por el usuario, no por el agente. |
| 4. Solicitudes y préstamos | Pendiente | Solicitudes en línea de socios activos y dirigentes sin ficha activa; ventanilla para representantes no socios con grupo obligatorio; identificar agrupación si el solicitante la representa; varios insumos por solicitud, dirigente responsable seleccionado, monto y garantía globales por préstamo. Pendiente no descuenta; aprobar reserva; entregar mantiene ocupación; devolución completa libera solo unidades utilizables. Bandejas separadas, recordatorio de atención, fecha comprometida y puntos rojos por pendientes/atrasos. Comprobar elegibilidad, cantidades, autoría, privacidad, fechas y operaciones simultáneas. |
| 5. Finanzas y comprobación | Pendiente | Vincular aportes/cobros y garantías de insumos con Finanzas; garantía separada del ingreso y retenciones conforme a las reglas existentes. Adaptar los archivos de tests y actualizar la evidencia de SPRINTS.md; los tests los ejecutará el usuario. No dar por superadas las nuevas reglas a partir de resultados anteriores. |

Las nuevas reglas sustituyen, para operaciones nuevas, las tarifas de sede y préstamos libres de la versión anterior. Las reservas, garantías, préstamos, proyectos y registros financieros existentes se conservarán sin reinterpretar sus condiciones. Reportes continúa pendiente; no se inició su implementación durante esta ampliación documental.

### Entrega 1 — Interfaz y navegación

- El usuario indicó continuar tras la actualización autorizada de requisitos. Esta entrega no modifica REQUIREMENTS.md. No se ejecutaron tests ni migraciones y no se instalaron dependencias.
- Menú organizado en Reservas de sede, Horarios de atención, Solicitudes y Finanzas; los grupos se abren al navegar por sus pantallas y muestran un punto rojo si su apartado tiene pendientes. Auditoría queda como último enlace. Los permisos efectivos de los módulos existentes se conservan.
- Calendario queda como consulta de ocupación e impresión. El formulario, detalle y acciones de reservas se trasladan a `/reservas`, protegido para presidente/secretario. Se mantienen validación de actividades, horarios, superposiciones y representante. El guardado regresa a la gestión; el detalle de arriendo reconoce el contexto Reservas además de Solicitudes y Finanzas.
- Reservas incluye los apartados Sede e Insumos; este último abre la gestión de inventario/préstamos existente. Los socios siguen accediendo a calendario y a sus solicitudes de arriendo. No se exponen gestión de inventario ni formularios de directiva a otros roles. La bandeja de solicitudes de insumos y la solicitud propia de dirigentes sin ficha se implementarán con su flujo en la entrega 4; no se muestran enlaces a funciones que todavía no existen.
- Mostrar/ocultar contraseña queda al lado del campo, conserva el funcionamiento sobre ambos campos y mantiene la generación de contraseña. El formulario compartido aplica el ajuste a creación y edición.
- Proyectos exige seleccionar un dirigente por identificador, validando su rol en servidor y guardando su nombre histórico. Migración `2026_10_20_000008_add_postulador_id_to_proyectos_table.php`, sin inferir vínculos a partir de nombres anteriores. Aplicar `php artisan migrate` antes de crear o cambiar postuladores. Al editar se puede conservar expresamente el nombre anterior; cambiar otros campos no lo reasigna ni modifica al registrador original.
- Etiqueta Representante de agrupación en la ficha administrativa y la solicitud propia del vecino, basada en el vínculo de la ficha o de su cuenta con el grupo. No se comparan nombres libres para adivinar identidades; no se crea ficha a representantes externos y no se alteran roles, estados ni permisos.
- Preparado `Integracion/NavegacionYConservacionDeDatosTest.php` para separación de calendario/gestión, permisos, etiqueta sin privilegios, selector de dirigentes, conservación histórica y contexto de regreso. Adaptado `Proyectos/RegistroYConsultaTest.php` al selector. Tests no ejecutados.
- Verificación técnica: sintaxis PHP correcta, ruta de gestión de reservas con middleware de presidente/secretario, Blade compilado y vistas afectadas revisadas con lint, assets compilados y diferencias sin errores de formato. No sustituye la comprobación funcional ni acredita ejecución de tests.
- Comprobación manual sugerida: recorrer desplegables con distintos roles; revisar puntos rojos y Auditoría al final; cambiar contraseña con el botón junto al campo; consultar Calendario y gestionar/cancelar desde Reservas; ir a Insumos y volver; abrir un arriendo desde Reservas y desde Finanzas; registrar proyecto con dirigente y editar uno antiguo conservando el nombre; comprobar etiqueta en una ficha inactiva sin obtener acceso a funciones de socio.
- Cobros y garantías conservan la lógica anterior hasta las entregas 3/5. Catálogo y edición de cantidades corresponden a la entrega 2; el flujo nuevo de solicitudes/préstamos corresponde a la 4. No se presenta esta entrega de interfaz como cumplimiento de esas reglas futuras.
- Corrección posterior al reporte del usuario: restaurada la definición de `$horasFin` en `ReservaController::index`, necesaria para calcular el fin de cada bloque de la grilla. Su eliminación al separar la gestión provocaba Undefined variable y dos fallos del test de interfaz. El usuario confirmó después que el test de interfaz funciona; el agente no ejecutó tests.
- Corrección de preparación de datos en `Proyectos/RegistroYConsultaTest.php`: el caso de filtro/paginación creaba proyectos directamente mediante el modelo con el identificador del postulador, pero omitía su nombre histórico obligatorio. Ahora incluye ese nombre desde el dirigente de prueba. No cambió la lógica de la aplicación ni la obligatoriedad del campo. El usuario confirmó posteriormente que el test funciona; el agente no ejecutó tests.

### Entrega 2 — Catálogo e inventario

- Se continúa tras la confirmación del usuario del test de Proyectos. No se modificó REQUIREMENTS.md, no se ejecutaron migraciones ni tests y no se instalaron dependencias.
- Migración `2026_10_20_000009_create_tipos_insumo_and_link_inventario.php`: catálogo con 15 tipos en cuatro categorías (mobiliario/eventos, equipamiento, herramientas, ayudas técnicas en comodato), vínculo opcional desde las existencias y unicidad para existencias nuevas de cada tipo. Aplicar `php artisan migrate` antes de entrar a Parámetros o Inventario.
- Los ítems previos conservan identificador, nombre, cantidad, préstamos e historial. Solo se vincula automáticamente un ítem a un tipo si coincide exactamente el nombre y existe un único ítem coincidente. No se fusionan filas duplicadas, no se adivinan equivalencias y no se crean cantidades de inventario al crear el catálogo. Los no vinculados se muestran sin categoría y conservan edición de cantidad y consulta.
- Administrador: consulta del catálogo por categorías y alta de nuevos tipos desde Parámetros, validando nombre único y categoría válida. No se agregan tarifas, garantías por insumo, eliminación ni edición de nombres del catálogo en esta entrega.
- Presidente/secretario: registro de existencias mediante selector del catálogo; el nombre se toma del servidor. Un tipo ya registrado se ajusta con Editar cantidad, sin crear otra fila. Restricción de unicidad y bloqueo del tipo protegen altas simultáneas. Los otros roles no pueden crear catálogo ni editar existencias.
- Edición de cantidad total entera no negativa, incluyendo cero si no hay unidades comprometidas. Comprobación de mínimo en servidor tras adquirir el mismo bloqueo de escritura del ítem usado por préstamos/devoluciones. No permite reducir por debajo de préstamos abiertos más unidades devueltas dañadas; conserva el nombre, vínculo de catálogo, registrador original y datos de préstamos. El cambio de cantidad se registra con el autor de la edición mediante la auditoría existente.
- El flujo nuevo de solicitudes y su reserva de existencias aún no existe. Su cómputo de unidades comprometidas se incorporará al implementar la entrega 4; no se atribuye a esta entrega cobertura de estados futuros.
- Preparado `tests/Feature/Inventario/CatalogoYExistenciasTest.php` para catálogo inicial, permisos de alta/edición, duplicados, nombres tomados del servidor, cantidades inválidas, mínimo de existencias, auditoría y conservación de registros anteriores. Adaptados los casos de alta de `Inventario/ExistenciasYPrestamosHistoricosTest.php` al selector. Tests no ejecutados; no se simula concurrencia real ni se acredita una ejecución automática de la migración sobre datos anteriores.
- Verificación técnica: sintaxis PHP correcta, rutas de catálogo exclusivas del administrador y de edición de cantidad para presidente/secretario, Blade compilado y vistas nuevas/modificadas revisadas con lint, assets compilados y diferencias sin errores de formato. No acredita ejecución de tests ni comprobación funcional.
- Comprobación manual sugerida: revisar las cuatro categorías con administrador y agregar un tipo; registrarlo en Inventario con presidente/secretario; aumentar y reducir cantidad; intentar reducir por debajo de lo prestado/dañado; revisar nombres y préstamos anteriores; comprobar que tesorero, vecino y administrador no editan cantidades y que presidente/secretario no amplían el catálogo.
- Siguiente entrega: reservas de sede con aporte variable y sin garantía para operaciones nuevas. La lógica de cobros anterior permanece hasta esa adaptación.
- Confirmación posterior del usuario: los tests y la página funcionan correctamente. Se continúa con la entrega 3; esta confirmación corresponde a Catálogo e inventario y no acredita todavía el funcionamiento de las reglas de la entrega siguiente.

### Entrega 3 — Reservas de sede con aporte variable

- Implementación autorizada al continuar tras la confirmación de Catálogo e inventario. No se modificó REQUIREMENTS.md, no se ejecutaron tests ni migraciones y no se instalaron dependencias.
- Migración `2026_10_20_000010_add_aporte_variable_to_reservas.php`: marca explícita para distinguir el régimen nuevo del histórico, autor y fecha de definición del aporte. Las filas anteriores quedan en su régimen original; no se recalculan montos ni se modifican garantías, ingresos, comprobantes o condiciones anteriores. Aplicar `php artisan migrate` antes de usar el flujo nuevo.
- El socio activo solicita el horario sin definir un precio ni adjuntar un pago anticipado. Presidente/secretario define el aporte una sola vez desde el detalle, guardando autor y fecha. En ventanilla se ingresa al crear la solicitud, para un socio activo o una persona sin ficha con los mismos datos personales exigidos previamente. No se crea ficha ni cuenta para esta última. Se conservan las comprobaciones de grupo y representante.
- Aporte entero no negativo por operación: $0 significa gratuita; un importe positivo requiere comprobante privado PDF/JPG/PNG de hasta 5 MB y verificación del pago para confirmar. En ventanilla se puede adjuntar al registrar o posteriormente; en línea se adjunta desde el detalle cuando la directiva ya definió el importe. Titular y directiva conservan el acceso autorizado. No se permite sustituir el comprobante, redefinir el importe ni modificar condiciones históricas con los endpoints nuevos.
- El horario permanece ocupado desde la solicitud pendiente. El plazo configurable se cuenta desde la creación y no se extiende al definir el aporte o adjuntar el archivo. Las operaciones nuevas comparten el bloqueo de escritura del calendario y releen el estado después de adquirirlo: no admiten definición, comprobante o confirmación tras cancelación/vencimiento. Se mantienen las comprobaciones de horarios, anticipación y superposiciones.
- Las gratuitas se confirman sin verificar pago ni garantía. Ninguna reserva del régimen nuevo crea garantía, incluso al confirmar o cancelar. Las históricas conservan validación de ambos pagos, registro financiero, cancelación con garantía por devolver y resolución de garantía completa/parcial según las reglas existentes.
- Finanzas para presidente/tesorero reutiliza el monto y comprobante de la reserva pagada, con concepto `aporte_reserva` (Aporte voluntario por reserva de sede), sin clasificación ordinaria/extraordinaria. Se impiden ingresos de $0 y duplicados vigentes; tras anulación se puede registrar nuevamente. El concepto se registra exclusivamente desde la operación vinculada, no desde el formulario genérico de ingresos. Los movimientos históricos conservan `arriendo`; las garantías históricas siguen separadas del saldo hasta su retención.
- Se retiraron los campos de tarifas fijas de la interfaz y validación de Parámetros, conservando las claves antiguas en la base para no alterar datos previos. Ya no determinan los aportes ni se actualizan desde ese formulario. Reservas incorpora un enlace a solicitudes para registrar aportes o solicitantes sin ficha. Detalle y Finanzas distinguen importes por definir, operaciones gratuitas, aportes y garantías históricas, sin asumir que toda reserva tiene garantía o comprobante.
- Preparado `tests/Feature/Reservas/AportesDeSedeTest.php`: permisos, autoría, importes inválidos, solicitud en línea, ventanilla gratuita, comprobante posterior, confirmación, ingreso vinculado, anulación y registro posterior, privacidad, vencimiento, cancelación, parámetros y conservación histórica. Adaptados `Reservas/SolicitudesYFinanzasDeSedeTest.php` y `Integracion/CertificadosYReservasDeVentanillaTest.php` en los casos de altas nuevas; se conservan los casos de garantías históricas con preparación explícita del régimen anterior. Tests no ejecutados; no se simula concurrencia real ni se acredita ejecución de la migración sobre una base histórica.
- Verificación técnica: sintaxis PHP correcta, rutas nuevas protegidas y roles de definición/confirmación y Finanzas revisados, Blade compilado y vistas afectadas con lint, assets compilados. Estas comprobaciones no sustituyen tests ni validación funcional del usuario.
- Comprobación manual sugerida: solicitar en línea y definir $0 con secretario, confirmar sin comprobante y comprobar que no permite ingreso; registrar en ventanilla un aporte distinto de la tarifa antigua y adjuntar su comprobante; probar definición y archivo posterior desde una solicitud en línea; verificar el pago y registrar el aporte con tesorero; anular y volver a registrar; revisar un arriendo histórico y resolver su garantía; intentar superposiciones y operaciones vencidas. Confirmar que otros vecinos no acceden al detalle ni a los archivos.
- Siguiente entrega: solicitudes y préstamos de insumos. Su elegibilidad, reserva de stock, varias líneas, dirigente responsable y garantías por préstamo siguen pendientes; no se atribuyen a esta entrega.
- Confirmación posterior del usuario: `Reservas/SolicitudesYFinanzasDeSedeTest.php`, `Integracion/CertificadosYReservasDeVentanillaTest.php` y `Reservas/AportesDeSedeTest.php` terminaron exitosamente y la aplicación funciona correctamente. El usuario solicita planificar el cierre del sprint 2 antes de seguir implementando.

## Plan de cierre pendiente del sprint 2 — revisión posterior a aportes variables

El alcance original del sprint 2 tiene implementación. La adaptación de reservas a aportes variables cuenta ahora con confirmación funcional y de tests del usuario. El cierre pendiente combina cobertura específica, revisión de permisos/trazabilidad y actualización de evidencia; no implica rehacer los bloques ya comprobados. Las secciones anteriores conservan observaciones históricas que deben reconciliarse al cerrar. Este plan no modifica REQUIREMENTS.md ni autoriza cambios futuros de ese archivo.

| Orden | Trabajo propuesto | Criterio de cierre |
| --- | --- | --- |
| 1 | Preparar cobertura específica del bloque 3a: generación mensual sin duplicados, edición/eliminación de horarios conservando reservas, cancelación de una ocurrencia, representante ausente, bloques de 30/60 minutos y superposiciones tras cambiar parámetros. Comprobar también privacidad del calendario e impresión semanal/mensual. | Tests preparados y ejecutados por el usuario; recorridos manuales de calendario e impresión confirmados. Corregir únicamente defectos encontrados. |
| 2 | Revisar el cierre de finanzas: cuotas/aportes y su clasificación, egresos, devoluciones, anulaciones, saldo, aportes de sede y garantías históricas. Completar evidencia de la corrección entre devolver y anular, en ambos órdenes. Distinguir las pruebas secuenciales de una comprobación real de simultaneidad. | Cada operación válida afecta una sola vez al saldo; las inválidas no dejan movimientos ni auditorías parciales. Tests y resultados registrados sin atribuir concurrencia real a envíos secuenciales. |
| 3 | Completar revisión y cobertura de auditoría financiera: fechas, autor, tipo de registro, combinaciones de filtros y paginación; fecha, IP, cambios y autor de movimientos; acceso exclusivo del presidente e inmutabilidad. Revisar permisos por endpoint y acceso a archivos de los módulos del sprint 2. | Consultas y filtros correctos, operaciones sin permiso rechazadas, archivos privados e historial conservado. Mantener parámetros exclusivos del administrador. |
| 4 | Reconciliar SPRINTS.md con REQUIREMENTS.md vigente y con las confirmaciones posteriores, separando notas históricas del estado actual. Registrar cualquier discrepancia real de permisos sin cambiar requisitos ni ampliar accesos silenciosamente. | Matriz de cumplimiento actual con evidencia y pendientes concretos; eliminación de estados pendientes obsoletos en el resumen vigente, conservando el contexto histórico. |
| 5 | Cerrar la dependencia financiera de insumos cuando se implemente su nuevo flujo: cobro vinculado, garantía por préstamo separada, devolución y retención parcial/total, anulación y trazabilidad. | Cumplimiento completo de la ampliación de RFS-58/62/63/64 con operaciones de insumos e historial de sede intacto. Depende de la entrega 4 y del componente financiero de la entrega 5; no se implementará antes de tener el préstamo nuevo. |
| 6 | Recorrido final de grupos, reservas, recurrencias, aportes gratuitos/pagados, finanzas y auditoría; ejecución por el usuario de los archivos afectados y registro del resultado. | Declarar completado el alcance original del sprint 2 cuando cierre su evidencia. Si la dependencia de insumos sigue abierta, indicarla expresamente y no declarar cumplida toda la ampliación financiera. |

Solicitudes/préstamos de insumos pertenecen funcionalmente al sprint 3, aunque sus cobros y garantías completen requisitos financieros compartidos con el sprint 2. Actas, proyectos y reportes financieros/listado de socios en Excel/PDF siguen clasificados en el sprint 3 y no se añaden como entregables originales faltantes del sprint 2. No se inició implementación nueva con esta planificación ni se ejecutaron tests.

### Cierre del sprint 2 — Paso 1: cobertura de calendario y recurrencias

- Se continúa con el cierre del sprint 2, según el orden acordado antes de retomar el sprint 3. Se revisaron generación mensual, edición/eliminación de horarios, cancelación, validación de bloques, ocupación del calendario e impresión. No se modificó lógica de aplicación ni REQUIREMENTS.md.
- Preparado `tests/Feature/Reservas/CalendarioYRecurrenciasTest.php`, con fecha de prueba fija para no depender del día real: generación completa por presidente/secretario, autor y representante guardados, repetición sin duplicados, omisión de ocurrencias pasadas, meses inválidos, edición que conserva reservas anteriores y afecta futuras, y eliminación que conserva reservas desvinculando el horario.
- Incluye cancelación de una sola ocurrencia sin regenerarla ni imprimirla como fijo, liberación de esa fecha, bloqueo de otras ocurrencias, representante ausente, horario fijo aún sin generar, bloques contiguos, validación de 30/60 minutos y límites de apertura/cierre. Comprueba también ocupación por intervalos reales al cambiar apertura/bloques, privacidad del calendario y prohibición de gestión/impresión para socios.
- Impresión semanal/mensual por presidente/secretario: rango de días, nombres de días en español, fijos aún no generados, ausencia de filas duplicadas y exclusión de reservas canceladas/vencidas.
- Verificación realizada: sintaxis PHP del archivo y correspondencia de rutas. El agente no ejecutó tests; quedan pendientes su ejecución y la confirmación del usuario. Las peticiones repetidas son secuenciales y no acreditan concurrencia real. La composición visual al imprimir requiere comprobación manual del navegador.
- Comando de comprobación para el usuario: `php artisan test tests/Feature/Reservas/CalendarioYRecurrenciasTest.php`. El siguiente paso será el cierre financiero y su evidencia, según el plan anterior.
- Corrección tras un fallo informado por el usuario: el caso de apertura 09:30 esperaba erróneamente ocupación en 09:30–10:30 para una reserva de 10:30–11:30. Ahora exige ocupación solo donde existe superposición y comprueba que los bloques contiguos anterior y posterior estén libres. No cambió la lógica de la aplicación. Sintaxis revisada; tests pendientes de nueva ejecución por el usuario.
- Confirmación posterior: el usuario indicó que el test funciona correctamente. Se registra ejecución satisfactoria de `Reservas/CalendarioYRecurrenciasTest.php` por el usuario; el agente no ejecutó tests. Esto no acredita por sí solo composición visual de impresión ni concurrencia real.

### Cierre del sprint 2 — Paso 2: comprobación financiera

- Revisados IngresoController, DevolucionController, EgresoController, los modelos y el trait de auditoría, junto con la cobertura existente de bloques 2a/2b y las pruebas de aportes de sede/garantías históricas ya confirmadas por el usuario. No se modificaron lógica de aplicación ni REQUIREMENTS.md.
- La devolución y anulación de ingresos comparten una transacción y una escritura de bloqueo sobre el mismo ingreso, seguida de recarga y comprobación del estado. Se conserva esta protección. La cobertura nueva comprueba ambos órdenes y reintentos secuenciales, sin atribuirle una ejecución simultánea real.
- Preparado `tests/Feature/Finanzas/ConsistenciaDelSaldoTest.php`: cuotas de socios/aportes de grupos, ordinarios/extraordinarios, con presidente/tesorero; se devuelve o anula primero, cambia el operador y se intentan ambas acciones de nuevo. Comprueba el saldo con otros ingresos y egresos presentes, conservación de montos/autores/motivos, ausencia de movimientos compensatorios y ausencia de auditorías adicionales por intentos rechazados.
- Incluye rechazo de devolución de conceptos no elegibles (incluidos aportes de sede), validación sin cambios persistidos, clasificación y autor tomados del flujo autorizado, rechazo de vínculos enviados a reservas/garantías desde el ingreso genérico y prohibición de registrar un aporte de sede sin su operación vinculada.
- Los casos existentes de egresos, comprobantes, saldo combinado, aportes de sede y resolución de garantías históricas se conservan en sus archivos, sin duplicar toda su cobertura. El archivo nuevo complementa esas pruebas; no demuestra recuperación ante fallos de infraestructura ni concurrencia real.
- Verificación: sintaxis PHP del archivo y revisión de rutas de ingresos. Tests no ejecutados por el agente; ejecutar `php artisan test tests/Feature/Finanzas/ConsistenciaDelSaldoTest.php`. Después corresponde completar filtros y permisos de auditoría, según el paso 3 del plan.
- Confirmación posterior: el usuario indicó que funciona correctamente. Se registra ejecución satisfactoria de la comprobación financiera por el usuario; no acredita concurrencia real y el agente no ejecutó tests.

### Cierre del sprint 2 — Paso 3: auditoría y permisos

- Revisados controlador, modelo, vista, migración y trait de auditoría, permisos de ruta y cobertura previa. La consulta está restringida al presidente; los modelos financieros incluidos son ingresos, egresos, devoluciones y garantías. Se mantienen las pruebas existentes de permisos financieros y comprobantes privados, sin duplicarlas íntegramente.
- Preparado `tests/Feature/Auditoria/ConsultaYProteccionTest.php`: categorías socio/certificado/usuario/finanzas, combinación de categoría/autor/fechas, límites inclusivos de día, filtros de fecha independientes, paginación con total/orden/enlaces y filtros preservados, y rechazo de fechas/autores/categorías inválidos sin modificar el historial.
- Incluye acceso prohibido para secretario/tesorero/administrador/vecino y redirección al inicio de sesión sin autenticación; consulta de registros sin usuario; inmutabilidad persistida frente a intentos de actualización y eliminación mediante el modelo. Las pruebas no atribuyen protección frente a acceso SQL directo al bloqueo del modelo.
- Un caso crea y anula un ingreso mediante sus endpoints y comprueba fecha de creación, autor real, IP y valores anteriores/posteriores de los cambios, incluyendo un autor enviado que no debe suplantar al usuario de sesión.
- Sin cambios de lógica de aplicación, permisos o REQUIREMENTS.md. Verificación de sintaxis PHP y middleware de la ruta. No se ejecutaron tests; comando para el usuario: `php artisan test tests/Feature/Auditoria/ConsultaYProteccionTest.php`. La siguiente etapa es reconciliar la documentación de cumplimiento con las confirmaciones vigentes, sin adelantar la ampliación pendiente de préstamos de insumos.
- Confirmación posterior: el usuario indicó que los tests terminaron correctamente. Se registra ejecución satisfactoria de `Auditoria/ConsultaYProteccionTest.php` por el usuario. El agente no ejecutó tests.

### Cierre del sprint 2 — Paso 4: matriz vigente de cumplimiento

Reconciliación documental con REQUIREMENTS.md vigente y las confirmaciones del usuario. No modifica requisitos, permisos ni lógica de aplicación. Esta matriz distingue implementación, evidencia y dependencias; sustituye como resumen actual a la revisión histórica del 16-10-2026.

| Área | Requisitos vigentes | Estado y evidencia |
| --- | --- | --- |
| Grupos y representantes | RFS-38 a 42; RFS-94 | Implementados: selección/autocompletado o representante manual sin ficha/cuenta, contacto independiente, conservación tras baja y bloqueo de nuevas reservas sin representante. La etiqueta no concede permisos. Existen pruebas de grupos/mejoras; no se atribuye una nueva ejecución integral de todos esos archivos a las confirmaciones del cierre. |
| Separación de calendario y gestión | RFS-43/44/55/56 | Implementada: Calendario de consulta, Reservas para gestión, catálogo gratuito y apartado de insumos. Pruebas de interfaz y comprobación funcional confirmadas previamente; `Reservas/CalendarioYRecurrenciasTest.php` confirma privacidad y restricciones de gestión/impresión. La bandeja nueva de solicitudes de insumos sigue pendiente en el sprint 3. |
| Bloques, recurrencias y superposiciones | RFS-45 a 49 | Implementados. `Reservas/CalendarioYRecurrenciasTest.php` confirmado por el usuario tras corregir una expectativa del test: generación mensual sin duplicados, fechas pasadas, conservación al editar/eliminar, cancelación de una ocurrencia y ocupación por intervalos al cambiar bloques/apertura. |
| Solicitud y aporte por reserva | RFS-50 a 54; RFS-61 | Implementados y comprobados: solicitud en línea/ventanilla, aporte definido por presidente/secretario, $0 sin pago, comprobante y verificación para importe positivo, vencimiento/cancelación y ausencia de garantía nueva. El usuario confirmó `Reservas/AportesDeSedeTest.php`, `Reservas/SolicitudesYFinanzasDeSedeTest.php` y `Integracion/CertificadosYReservasDeVentanillaTest.php`. |
| Horario imprimible | RFS-57 | Implementado. Funcionamiento manual confirmado anteriormente; impresión semanal/mensual, idioma, rangos, exclusiones y ausencia de duplicados cubiertos por `Reservas/CalendarioYRecurrenciasTest.php`, confirmado por el usuario. La composición visual final de impresión no se acredita mediante las aserciones HTML. |
| Ingresos, cuotas y aportes de sede | RFS-58 a 60; parte de RFS-64 | Implementados para los conceptos actuales: clasificación de cuotas/aportes de grupos, comprobación de medio de pago, ingreso de aporte de sede vinculado, sin ingresos de $0 ni duplicados vigentes. Tests de reservas y `Finanzas/ConsistenciaDelSaldoTest.php` confirmados. El concepto y registro vinculado a préstamos nuevos quedan pendientes. |
| Egresos, devoluciones, anulaciones y saldo | RFS-65 a 68 | Implementados. Cobertura existente de 2a/2b y nueva comprobación de cierre confirmada: ambos órdenes devolver/anular, cambio de operador, reintentos, conservación del movimiento ganador, saldo y ausencia de auditorías adicionales por rechazo. No acredita simultaneidad real. |
| Garantías históricas de sede | Parte histórica de RFS-62/63/64; RFS-100 | Conservadas y gestionables: recibida/por devolver/devuelta/retenida, devolución completa o retención parcial y devolución del resto, sin contabilizar dos veces el dinero. Casos históricos conservados en `Reservas/SolicitudesYFinanzasDeSedeTest.php`, cuya ejecución actual confirmó el usuario. |
| Auditoría y permisos del sprint 2 | RFS-85 a 87; dependencias RFS-81/82/83/88 | Implementados. `Auditoria/ConsultaYProteccionTest.php` confirmado: categorías, autor/fechas, paginación, validación, acceso exclusivo del presidente, IP/autor/cambios e inmutabilidad por el modelo. Los permisos financieros y archivos privados conservan las pruebas de sus módulos. Parámetros sigue exclusivo del administrador. |
| Cobros y garantías de préstamos de insumos | Parte de RFS-58/62/63/64; RFS-97/100 | Pendientes de implementación del flujo nuevo: monto y garantía globales por préstamo, registro financiero vinculado, resolución y trazabilidad. Dependencia de las entregas 4/5 de la ampliación del sprint 3. No se declaran cumplidos estos requisitos en su totalidad por haber comprobado garantías históricas de sede. |

**Resultado del cierre documental:** los pasos 1, 2 y 3 de comprobación cuentan con tests ejecutados satisfactoriamente por el usuario; el paso 4 queda documentado. No se identificó en esta revisión una nueva corrección de aplicación pendiente del alcance original del sprint 2. La ampliación financiera de insumos permanece abierta y se completará con el sprint 3. El paso 6 mantiene el recorrido final conjunto y el registro de regresión para cuando se cierre esa integración; no se presenta como ya ejecutado.

**Aclaración de permisos conservada:** RFS-81 mantiene una formulación general de acceso del presidente, mientras RFS-83 y las rutas reservan usuarios/roles al administrador. Esta discrepancia transversal sigue documentada; no se amplían accesos ni se cambia REQUIREMENTS.md sin una autorización específica. No afecta al permiso de consulta de auditoría, explícito en RFS-87 y comprobado.

**Evidencia confirmada en este cierre:** `Reservas/CalendarioYRecurrenciasTest.php`, `Finanzas/ConsistenciaDelSaldoTest.php` y `Auditoria/ConsultaYProteccionTest.php`. También se conserva la confirmación anterior de `Reservas/SolicitudesYFinanzasDeSedeTest.php`, `Integracion/CertificadosYReservasDeVentanillaTest.php` y `Reservas/AportesDeSedeTest.php`. Son ejecuciones del usuario por archivo; no se afirma una regresión completa conjunta ni pruebas simultáneas reales en Windows/Ubuntu.

**Próximo trabajo propuesto:** retomar la entrega 4 del sprint 3 (solicitudes y préstamos de insumos), seguida de su integración financiera y reportes. Esta actualización solo registra el cierre documental; no inicia esa implementación ni instala dependencias.
