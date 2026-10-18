<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', fn (Blueprint $table) => $table->string('representante_rut', 12)->nullable());
        Schema::table('solicitudes_insumos', fn (Blueprint $table) => $table->string('solicitante_rut', 12)->nullable());
    }

    public function down(): void
    {
        Schema::table('solicitudes_insumos', fn (Blueprint $table) => $table->dropColumn('solicitante_rut'));
        Schema::table('grupos', fn (Blueprint $table) => $table->dropColumn('representante_rut'));
    }
};
