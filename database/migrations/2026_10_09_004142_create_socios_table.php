<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('socios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->string('nombre_completo');
            $table->string('rut', 12)->unique();
            $table->string('email')->nullable();
            $table->string('direccion');
            $table->string('telefono', 20);
            $table->date('residente_desde');
            $table->string('carnet_path')->nullable();
            $table->string('boleta_path')->nullable();
            $table->enum('estado', ['pendiente', 'por_firmar', 'activo', 'rechazado'])->default('pendiente');
            $table->text('observaciones')->nullable();
            $table->date('fecha_ingreso')->nullable();
            $table->foreignId('revisado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('socios');
    }
};
