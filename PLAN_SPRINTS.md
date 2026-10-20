# Plan de sprints

## Sprint 1: MUST HAVE

**Objetivo:** Lo indispensable para que el sistema exista y resuelva el trámite central: cuentas, roles, socios y certificados.

**Entregables:**

- Configuración del entorno web y base de datos central.
- Roles y permisos: Administrador, Presidente, Secretario, Tesorero y Vecino. La condición de socio se deduce de la ficha activa.
- Parámetros configurables administrados exclusivamente por el administrador: horario de sede, bloques, vigencia y descarga de certificados, plazos y aporte sugerido de residencia.
- Registro de vecinos y solicitud de inscripción, con validación de antecedentes, rechazo con motivo y reapertura para corregir.
- Agenda de citas para la firma presencial del libro de socios y horarios públicos de atención independientes.
- Gestión de socios: registro en ventanilla, búsqueda, edición, baja, reactivación y creación de cuenta vinculada. Baja de dirigentes reservada al administrador.
- Certificados de residencia: solicitud en línea o ventanilla, aporte voluntario incluido $0, revisión, emisión con folio y PDF, descarga con plazo, retiro, rechazo, anulación y validación pública.
- Certificado de socio gratuito: emisión autónoma para socios activos y emisión en ventanilla solo para impresión.
- Registro automático como ingreso del aporte positivo del certificado de residencia, sin ingresos de $0.
- Auditoría de socios, certificados y usuarios, consultable exclusivamente por el presidente.
- Gestión administrativa de cuentas y roles; eliminación de cuenta según permisos, conservando fichas e historial.

## Sprint 2: SHOULD HAVE

**Objetivo:** Funcionalidades de alto valor para la gestión diaria de la sede: quién la usa, cuándo y qué aportes y garantías se reciben.

**Entregables:**

- Agrupaciones con representante vigente, sin exigir ficha de socio ni cuenta de usuario.
- Calendario de consulta de ocupación, separado de la gestión de reservas, con impresión semanal y mensual.
- Reservas de sede en línea para socios activos y en ventanilla para vecinos sin ficha ni cuenta; actividades y horarios fijos de agrupaciones con generación mensual.
- Aporte voluntario y garantía opcional por reserva, sin tarifas fijas; comprobante y verificación de importes positivos, confirmación, cancelación y vencimiento de pendientes.
- Finanzas: ingresos, cuotas y aportes de agrupaciones ordinarios o extraordinarios, egresos, devoluciones, anulaciones y saldo de caja.
- Garantías separadas de los ingresos, con devolución o retención total/parcial por daños.
- Ingresos por aprobar dentro de Ingresos y registro financiero separado de la confirmación de reservas.
- Auditoría financiera y filtros por fecha, autor y tipo de registro.

## Sprint 3: COULD HAVE

**Objetivo:** Módulos complementarios de transparencia, gestión de insumos y generación de reportes.

**Entregables:**

- Actas de asamblea y directiva, con documentos opcionales y acceso según ficha activa o cargo.
- Proyectos postulados, adjudicados o rechazados, gestionados por el presidente y consultables por todo usuario con sesión iniciada.
- Catálogo de insumos administrado por el administrador y existencias gestionadas por presidente o secretario.
- Solicitudes de varios insumos en línea o ventanilla, aprobación con reserva de existencias, responsable automático, entrega, devolución completa, daños y avisos de atraso.
- Aporte y garantía globales por préstamo, con registro financiero, devolución o retención y conservación del historial.
- Reportes financieros por período y agrupación, exportables a PDF; listado de socios activos en Excel y PDF.

## Sprint 4: TESTING & BUFFER

**Objetivo:** Validar los flujos completos, corregir errores y ajustar el diseño antes de la entrega del MVP.

**Entregables:**

- Pruebas de permisos, validaciones y recorridos completos de socios, certificados, reservas, préstamos y finanzas.
- Corrección de errores y pruebas de regresión e integración.
- Ajustes de navegación, formularios, tablas, mensajes y presentación en distintas dimensiones de pantalla.
- Revisión de consistencia de existencias, saldo, garantías e históricos.
- Registro de resultados y pendientes de validación, evidencias.
