# Datos de demostración

Ejecutar desde la raíz del proyecto, con `APP_ENV=local`:

```sh
php artisan db:seed --class=DemoSeeder
```

La carga es independiente de `DatabaseSeeder`. No borra registros ni modifica cuentas existentes. Si encuentra `admin@demo.example`, termina sin volver a cargar ni restablecer escenarios: conserva las acciones que hayas realizado. Las fechas se calculan al cargar y después avanzan normalmente; los pendientes pueden vencer y los enlaces de descarga caducar.

Todos los nombres de ejemplo comienzan con **DEMO**. Los documentos adjuntos son muestras ficticias; los certificados se generan desde la aplicación como de costumbre. Las cifras de demostración forman parte del saldo y de los reportes de esta base local.

## Cuentas

Contraseña de todas las cuentas nuevas: **`DemoJJVV2026!`**.

| Correo | Acceso o escenario |
| --- | --- |
| admin@demo.example | Usuarios, roles, parámetros y catálogos |
| presidente@demo.example | Gestión general y auditoría; ficha activa |
| secretario@demo.example | Gestión de socios, certificados, sede e insumos; ficha activa |
| tesorero@demo.example | Finanzas y reportes; ficha activa |
| activo-1@demo.example | Socio activo, representante, certificados y solicitudes personales |
| activo-2@demo.example | Otro socio activo con trámites propios |
| pendiente-1@demo.example | Inscripción pendiente |
| por_firmar-1@demo.example | Aprobado con cita reservada |
| rechazado-1@demo.example | Inscripción rechazada con motivo |
| inactivo-1@demo.example | Ficha dada de baja |
| sin-ficha@demo.example | Vecino registrado sin inscripción |

También existen las cuentas terminadas en `-2` para cada estado de ficha. El ejemplo `-3` de cada estado es de ventanilla, sin cuenta. Hay tres fichas por estado y tres fichas activas adicionales para los dirigentes DEMO. La cuenta de administrador no tiene ficha.

## Recorridos manuales

1. **Socios y citas:** filtra por estado y busca DEMO. Revisa antecedentes, rechaza o aprueba pendientes, reactiva inactivos y activa fichas por firmar tras la firma. Hay dos citas reservadas y cuatro libres; `por_firmar-3` no tiene cita ni cuenta.
2. **Certificados:** hay tres ejemplos por caso: pendiente, emitido reciente, rechazado, anulado, caducado, descargado, retirado, plazo de descarga vencido sin retiro, certificado de socio y certificado de socio para impresión. Los destinatarios identifican el caso. Incluye residencia con aporte $0 y positivo; los aportes emitidos ya tienen ingreso. Valida públicamente con el código visible en cada certificado.
3. **Sede:** busca motivos DEMO. Hay tres ejemplos por caso: pendiente, confirmada con aporte por registrar, cancelada con garantía por devolver, vencida, realizada en el pasado, gratuita, de agrupación, de junta y con ingreso anulado pendiente de reingreso. Revisa también el próximo mes: un grupo tiene horario fijo y sus reservas semanales generadas.
4. **Agrupaciones:** hay un representante socio activo, otro externo sin cuenta ni ficha y un grupo sin representante para revisar esa restricción.
5. **Insumos:** hay tres solicitudes por caso: pendiente, aprobada, sin comprobante, por validar pago, entregada, atrasada, devuelta y devuelta con daños. Cada solicitud tiene dos insumos. Hay operaciones gratuitas y positivas, por internet y ventanilla. El catálogo DEMO añade sillas, mesas y bastones con 100 unidades de cada uno; los bastones quedan completamente disponibles. Los daños dejan unidades fuera de servicio.
6. **Ingresos:** revisa pendientes de sede e insumos, aportes vigentes y anulados, cuotas ordinarias/extraordinarias y cuotas devueltas. Las reservas DEMO de reingreso permiten registrar nuevamente un aporte anulado sin duplicar garantía.
7. **Garantías:** revisa recibidas, por devolver, devueltas y retenidas; hay retenciones totales y parciales con devolución del resto. Solo la retención figura como ingreso.
8. **Egresos:** hay gastos de servicios básicos y mantención, vigentes y anulados, con respaldo ficticio.
9. **Actas y proyectos:** hay tres actas de cada tipo, con y sin adjunto, y tres proyectos por estado. Comprueba las vistas personales con un socio activo, uno inactivo y un dirigente.
10. **Auditoría y reportes:** entra como presidente para revisar la carga y los cambios posteriores. Consulta reportes con intervalos que incluyan los últimos meses y el próximo mes para reservas recurrentes.

Los registros se insertan en sus estados finales para cubrir escenarios; no representan un historial completo de transiciones realizadas mediante formularios. La auditoría conserva las altas y operaciones registradas por los modelos. No se ejecutan pruebas automatizadas.

La atención pública existente se conserva; solo si está vacía se agregan martes y jueves de 10:00 a 12:00. Los horarios de sede, bloques y plazos configurados se respetan. La carga se revierte en la base si falla; el PDF de muestra puede permanecer para reutilizarlo en el siguiente intento.
