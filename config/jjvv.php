<?php

return [
    // Nombre que aparece en los certificados.
    'nombre' => env('JJVV_NOMBRE', 'Junta de Vecinos'),

    // Días de vigencia de un certificado desde su emisión.
    'certificado_vigencia_dias' => (int) env('JJVV_CERTIFICADO_VIGENCIA_DIAS', 90),

    // Aporte sugerido por el certificado de residencia (CLP).
    'aporte_residencia' => (int) env('JJVV_APORTE_RESIDENCIA', 1000),
];
