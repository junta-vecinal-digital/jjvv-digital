<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['actividad' => 'Actividad de la junta', 'comunitaria' => 'Actividad comunitaria', 'grupo' => 'Actividad de agrupación', 'particular' => 'Actividad privada', 'taller' => 'Taller'] as $clave => $nombre) {
            $existente = DB::table('tipos_actividad')->where('clave', $clave)->first();
            if ($existente) {
                DB::table('tipos_actividad')->where('id', $existente->id)->update(['nombre' => $nombre, 'updated_at' => now()]);
            } else {
                DB::table('tipos_actividad')->insert(['clave' => $clave, 'nombre' => $nombre, 'activo' => true, 'created_at' => now(), 'updated_at' => now()]);
            }
        }
    }

    public function down(): void
    {
        // No se eliminan actividades que podrían estar en uso ni se alteran registros históricos.
    }
};
