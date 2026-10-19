# Sprint 4: comprobación final del MVP

Estado: cerrado en entorno local el 18/10/2026 por instrucción del usuario, quien confirmó migración, tests posteriores a los últimos cambios y revisión manual del aporte $0. Los bloques anteriores y el recorrido integrado ya contaban con confirmaciones previas. El agente no ejecutó pruebas ni migraciones. Evidencias de entrega no producidas, concurrencia real sin acreditar y despliegue aplazado.

La guía siguiente queda como referencia para futuras regresiones; no indica repetir pruebas ahora ni acredita escenarios adicionales a los confirmados.

## 1. Regresión automática local

Desde CMD, en la carpeta del proyecto:

```cmd
php artisan config:clear
php artisan migrate
php artisan test tests/Feature/Integracion/AportesCertificadosYComprobantesTest.php
php artisan test
```

`phpunit.xml` configura SQLite en memoria para las pruebas, correo y sesión en memoria y cola síncrona. Comprobar que no existan variables de entorno externas que sustituyan esa configuración. No usar una base con datos reales para ejecutar tests. No es necesario volver a ejecutar cada archivo por separado si la suite completa termina correctamente.

Registrar el resultado completo: pruebas aprobadas, fallidas y omitidas. Si falla, conservar el nombre del test y el mensaje; corregir primero el caso antes de repetir la regresión. El agente no ejecuta tests.

La migración 000014 agrega identidad histórica del otorgante y respaldo privado a ingresos; comprobar residencia con aporte cero/positivo, importe conservado y descarga de socio sin auditoría. La migración 000013 agrega RUT a representantes y solicitudes de insumos, conservando los históricos sin inventar identificaciones. En cada dirigente, revisar Mi ficha de socio: completar inscripción, revisión y activación por otra persona. Comprobar que tesorero activo pueda solicitar sede y certificado de residencia; sin ficha activa conserva su cargo, pero no sus trámites personales.

## 2. Recorrido manual final

Usar datos de demostración y sesiones separadas para cada rol. Los escenarios ya comprobados por bloque sirven de base; esta pasada comprueba que la navegación y los resultados sigan siendo coherentes en conjunto.

| Área | Comprobación | Resultado esperado |
| --- | --- | --- |
| Sesiones y permisos | Navegar como administrador, presidente, secretario, tesorero y vecino; probar también una URL restringida directamente. | Menús acordes al rol y rechazo del acceso no autorizado. Parámetros solo para administrador; auditoría solo para presidente. |
| Socios y certificados | Solicitar inscripción con RUT incorrecto/correcto, revisar documentación y ficha; emitir certificado de socio desde su cuenta y desde ventanilla. | Validación en español; certificado autónomo inmediato y emisión de ventanilla disponible para imprimir. Certificado de residencia conserva su flujo propio. |
| Grupos y calendario | Consultar grupo con representante externo, crear reserva y probar un intervalo superpuesto. | Representación conservada, calendario de consulta separado de gestión y superposición rechazada. |
| Reservas de sede | Recorrer una reserva gratuita y otra con aporte definido por directiva, comprobante y validación financiera. | Importe y estado correctos, sin garantía nueva de sede; históricos conservados. |
| Insumos y finanzas | Revisar solicitud, aprobación, entrega, devolución con daño y resolución de garantía; consultar el reporte del grupo. | Disponibilidad coherente, dañados fuera de servicio y garantía separada del ingreso; sin cobros ni devoluciones duplicados. El test integrado ya aprobado cubre este recorrido automáticamente. |
| Avisos y atención | Revisar puntos rojos de pendientes/atrasos y horarios públicos en portada sin iniciar sesión. | Solo presidente/secretario ven los avisos previstos; las citas de firma no quedan restringidas a horarios de atención. |
| Actas y proyectos | Abrir acta de asamblea/directiva según rol y consultar un proyecto con su postulador dirigente. | Permisos también en archivos privados; datos del proyecto e historial conservados. |
| Reportes y presentación | Descargar PDF financiero y listado de socios en PDF/Excel; revisar selectores, fechas, adjuntos y vista de impresión. | Español, fechas dd/mm/aaaa con selección directa de mes y año, flechas de selectores separadas del texto, columnas sin cortes y socios activos sin datos de contacto. |
| Auditoría | Consultar un registro de socio y un movimiento financiero con filtros de fecha/autor/tipo. | Autor y cambios trazables; sin controles de edición o eliminación. |

## 3. Comprobaciones separadas de entorno

- Ubuntu/AWS: aplazado por instrucción del usuario; pendiente de preparar y comprobar en otro momento. Esta guía no autoriza cambios en el servidor. Revisar después compatibilidad de PHP/extensiones con Composer, ZIP para Excel, permisos de almacenamiento privado, assets compilados, migraciones y configuración de español/zona horaria.
- Scheduler: el vencimiento automático de solicitudes usa `arriendos:liberar-vencidos` cada minuto. En desarrollo puede mantenerse `php artisan schedule:work` en otra consola; en Ubuntu requiere la configuración correspondiente. No ejecutar deliberadamente vencimientos sobre datos reales para una demostración.
- Concurrencia real: SQLite en memoria no acredita dos operadores aprobando/cobrando simultáneamente. Comprobarlo por separado en una base de prueba del motor del despliegue antes de afirmar esa validación.
- RFS-81/83 precisados con autorización: administración de usuarios, roles, parámetros y catálogos exclusiva del administrador. Otros hallazgos de cierre están en [REVISION_REQUISITOS.md](REVISION_REQUISITOS.md).

## Registro de cierre

Cierre local registrado el 18/10/2026 a partir de la confirmación del usuario sobre migración, tests posteriores y revisión manual del aporte $0, junto con la aceptación previa de los bloques. No se registran cantidades de tests ni resultados de escenarios adicionales que el usuario no haya comunicado. Las comprobaciones de servidor y concurrencia permanecen fuera de este cierre.
