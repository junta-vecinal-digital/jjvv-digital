<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prestamos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_inventario_id')->constrained('items_inventario')->restrictOnDelete();
            $table->unsignedInteger('cantidad');
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->string('destinatario_nombre');
            $table->string('responsable_nombre');
            $table->string('responsable_telefono', 20);
            $table->date('fecha_solicitud');
            $table->date('fecha_prestamo');
            $table->string('estado_salida', 20);
            $table->date('fecha_devolucion')->nullable();
            $table->string('estado_regreso', 20)->nullable();
            $table->unsignedInteger('cantidad_danada')->default(0);
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('devuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['item_inventario_id', 'fecha_devolucion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prestamos');
    }
};
