# Historial resumido de sprints

## 1. Sprint 1 — MUST HAVE: base del sistema

**Resultado:** completado.

- Se implementaron cuentas, roles, permisos y parámetros del sistema.
- Se completó el proceso de inscripción: antecedentes, revisión, rechazo, reapertura, cita de firma y activación de socios.
- Se incorporaron registro presencial, búsqueda, edición, baja, reactivación y creación de cuentas vinculadas a fichas.
- Se implementaron certificados, emisión en PDF, folios, descarga, retiro y verificación pública, junto con ingresos base y auditoría.
- Las mejoras posteriores incorporaron formularios por pasos, presentación en español, horarios públicos, emisión autónoma del certificado de socio y avisos de pendientes.

## 2. Sprint 2 — SHOULD HAVE: agrupaciones, sede y finanzas

**Resultado:** alcance original implementado y cierre revisado; integración financiera de insumos completada posteriormente con el sprint 3.

- Se implementaron agrupaciones y representantes, calendario, horarios fijos, generación mensual, control de superposiciones e impresión semanal/mensual.
- Se completaron solicitudes de sede, confirmación, vencimiento y cancelación; ingresos, egresos, cuotas, devoluciones, anulaciones, garantías y saldo de caja.
- La revisión del 16/10 detectó un riesgo de doble descuento al devolver y anular una cuota. Se corrigió mediante bloqueo y validación compartidos; la comprobación posterior del usuario fue satisfactoria.
- Se aclaró que una garantía recibida no es ingreso: solo su parte retenida por daños se incorpora al saldo de ingresos.
- Desde el 17/10 se ampliaron los representantes a personas sin ficha ni cuenta, conservando su designación tras la baja como socio. Los parámetros y catálogos quedaron reservados al administrador.
- Se separó el calendario de consulta de la gestión de reservas y se habilitó ventanilla para personas sin ficha ni cuenta.
- Las tarifas fijas de sede fueron sustituidas por aportes definidos por operación, incluido $0, conservando condiciones y garantías históricas. La garantía opcional para reservas nuevas se reincorporó en los ajustes del 19/10.
- El usuario confirmó las comprobaciones de reservas, recurrencias, consistencia del saldo y auditoría. La dependencia financiera de insumos se cerró durante el sprint siguiente.

## 3. Sprint 3 — COULD HAVE: transparencia, insumos y reportes

**Resultado:** módulos e integración comprobados por el usuario.

- **Actas:** registro de asamblea y directiva con adjuntos privados y consulta según ficha activa o cargo. Funcionamiento y pruebas confirmados.
- **Proyectos:** registro y edición por el presidente y consulta autenticada. La postulación externa pasó de nombre libre a selección de dirigente, conservando nombres históricos y distinguiendo al registrador del sistema.
- **Inventario:** el registro inicial de ítems y préstamos evolucionó a un catálogo por categorías, ampliable por el administrador, con existencias editables por presidente/secretario.
- **Solicitudes y préstamos:** se incorporaron varias líneas de insumos, aprobación con reserva de unidades, entrega, devolución completa, daños fuera de servicio y avisos de atraso. Las reglas de solicitantes y responsable se precisaron después, durante los ajustes finales.
- **Finanzas de insumos:** aporte y garantía globales por préstamo, recepción vinculada, comprobantes privados, resolución de garantía tras devolver los insumos y reingreso de aportes o retenciones anulados sin duplicar garantías.
- **Reportes:** finanzas por período y agrupación con PDF; socios activos en Excel/PDF, sin datos de contacto. Los movimientos sin vínculo se muestran como «Sin agrupación».
- El usuario confirmó los bloques y el recorrido integrado de préstamo, existencias, cobro, garantía, devolución con daños, retención, reporte y anulación/reingreso.

## 4. Sprint 4 — TESTING & BUFFER: revisión y cierre local

**Resultado:** cierre local confirmado el 18/10/2026, seguido de nuevas entregas.

- Se preparó la regresión por módulos y el recorrido manual por cargos; el usuario confirmó una ejecución conjunta de la suite antes de los últimos ajustes de cierre.
- Se exigió ficha activa a los dirigentes para sus trámites personales, conservando permisos operativos. Se bloqueó la resolución de la ficha propia y se precisó el RUT de representantes y solicitantes.
- Se unificó la gestión de reservas y se ajustaron navegación, fechas en español, identidad visual y verificación pública de certificados.
- La revisión final de requisitos resolvió cinco asuntos: elegibilidad de dirigentes, aporte voluntario de residencia, identidad histórica del otorgante, auditoría de descargas y archivos de respaldo en ingresos manuales.
- Se admitió aporte $0 en residencia sin respaldo ni ingreso; los aportes positivos conservaron revisión y registro automático del importe elegido. Las descargas de certificados de socio quedaron fuera de auditoría.
- Se guardaron nombre/cargo del otorgante al emitir y se incorporaron comprobantes privados a ingresos manuales.
- El usuario confirmó migración, pruebas posteriores y revisión manual del aporte $0; se registró el cierre local del MVP.
- Las pruebas se reorganizaron por módulos conservando su contenido. El usuario confirmó su ejecución y el funcionamiento manual tras la reorganización.

## 5. Ajustes posteriores al cierre

### Operaciones y permisos

- Se afinó la navegación de reservas y el autocompletado de representantes por vínculo o RUT; el usuario confirmó las pruebas de este ajuste.
- Inventario quedó separado, con registro/edición de existencias e historial de consulta; las operaciones de préstamos permanecieron en Reservas.
- Se habilitaron solicitudes de insumos en ventanilla para cualquier vecino sin ficha ni cuenta, con nombre, RUT válido, domicilio y teléfono. En línea se mantuvo ficha activa.
- Se precisaron actividades de sede y el campo de representación, habilitado únicamente para agrupaciones.
- La baja de fichas de dirigentes quedó reservada al administrador. Dirigentes y administradores no pueden eliminar su propia cuenta.
- El responsable de préstamo pasó de selección manual a asignación automática: registrador en ventanilla o aprobador en línea, exclusivamente presidente/secretario, conservando nombre y cargo históricos.
- Se añadió garantía opcional de sede y registro financiero de aportes/garantías pendientes, sin ingresos de $0 ni duplicación de garantías.

### Diseño y regresión

- Se implementó el diseño aprobado: menú lateral, Mi cuenta, socios activos/pendientes/archivo, formularios por etapas y detalles o edición desplegables en los listados.
- Finanzas agrupó Ingresos y Egresos, integró los pendientes dentro de Ingresos y reunió garantías de sede e insumos en Registro garantías. Estas diferencias de interfaz fueron autorizadas manteniendo REQUIREMENTS.md sin cambios.
- Se adaptaron paneles, calendario, reportes, administración y pantallas públicas; se conservó la recuperación de datos y errores de formularios.
- Se actualizaron las pruebas al diseño nuevo. Una ejecución del seuit de tests detectó un detalle de Proyectos que no se renderizaba; se corrigió y quedó operativo.
