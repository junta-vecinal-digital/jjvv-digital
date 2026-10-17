<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipos_insumo', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('categoria', 30);
            $table->timestamps();
        });
        $categorias = [
            'mobiliario' => ['Sillas', 'Mesas', 'Toldos', 'Carpas estructurales', 'Vajilla', 'Mantelería'],
            'equipamiento' => ['Equipos de amplificación'],
            'herramientas' => ['Herramientas de jardinería', 'Herramientas eléctricas', 'Escalas o escaleras extensibles'],
            'ayudas_tecnicas' => ['Catres clínicos', 'Sillas de ruedas', 'Burritos', 'Muletas', 'Bastones'],
        ];
        foreach ($categorias as $categoria => $nombres) {
            foreach ($nombres as $nombre) {
                DB::table('tipos_insumo')->insert([
                    'nombre' => $nombre, 'categoria' => $categoria, 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
        }
        Schema::table('items_inventario', function (Blueprint $table) {
            $table->foreignId('tipo_insumo_id')->nullable()->unique()->constrained('tipos_insumo')->restrictOnDelete();
        });
        foreach (DB::table('tipos_insumo')->get() as $tipo) {
            // No fusiona existencias ni adivina equivalencias entre nombres diferentes.
            $coincidencias = DB::table('items_inventario')->where('nombre', $tipo->nombre)->get(['id', 'nombre'])
                ->filter(fn ($item) => $item->nombre === $tipo->nombre);
            if ($coincidencias->count() === 1) {
                DB::table('items_inventario')->where('id', $coincidencias->first()->id)->update(['tipo_insumo_id' => $tipo->id]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('items_inventario', function (Blueprint $table) {
            $table->dropUnique(['tipo_insumo_id']);
            $table->dropConstrainedForeignId('tipo_insumo_id');
        });
        Schema::dropIfExists('tipos_insumo');
    }
};
