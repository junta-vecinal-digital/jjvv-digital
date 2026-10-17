<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('solicitudes_insumos', function (Blueprint $table) {
            $table->id();
            $table->string('estado', 20)->default('pendiente')->index();
            $table->foreignId('solicitante_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('socio_id')->nullable()->constrained('socios')->nullOnDelete();
            $table->foreignId('grupo_id')->nullable()->constrained('grupos')->nullOnDelete();
            $table->string('solicitante_nombre');
            $table->string('grupo_nombre')->nullable();
            $table->string('telefono_contacto', 20);
            $table->string('direccion_contacto')->nullable();
            $table->string('correo_contacto')->nullable();
            $table->string('canal', 20);
            $table->date('fecha_comprometida');
            $table->foreignId('responsable_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('responsable_nombre')->nullable();
            $table->unsignedInteger('monto')->nullable();
            $table->unsignedInteger('garantia')->nullable();
            $table->foreignId('registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('aprobado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprobado_en')->nullable();
            $table->foreignId('entregado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_entrega')->nullable();
            $table->foreignId('devuelto_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_devolucion')->nullable();
            $table->timestamps();
        });
        Schema::table('prestamos', function (Blueprint $table) {
            $table->foreignId('solicitud_insumo_id')->nullable()->constrained('solicitudes_insumos');
        });
        Schema::create('solicitud_insumo_lineas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('solicitud_insumo_id')->constrained('solicitudes_insumos');
            $table->foreignId('item_inventario_id')->constrained('items_inventario');
            $table->string('nombre_insumo');
            $table->unsignedInteger('cantidad');
            $table->foreignId('prestamo_id')->nullable()->unique()->constrained('prestamos');
            $table->unique(['solicitud_insumo_id', 'item_inventario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('solicitud_insumo_lineas');
        Schema::table('prestamos', fn (Blueprint $table) => $table->dropConstrainedForeignId('solicitud_insumo_id'));
        Schema::dropIfExists('solicitudes_insumos');
    }
};
