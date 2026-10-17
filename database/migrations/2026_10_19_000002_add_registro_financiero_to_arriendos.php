<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->foreignId('reserva_id')->nullable()->constrained('reservas');
            $table->foreignId('garantia_id')->nullable()->constrained('garantias');
        });
        Schema::table('garantias', function (Blueprint $table) {
            $table->timestamp('registrada_en')->nullable();
            $table->unsignedBigInteger('registrada_por')->nullable();
            $table->date('fecha_pago')->nullable();
            $table->string('medio_pago', 20)->nullable();
            $table->string('talonario', 30)->nullable();
            $table->date('fecha_resolucion')->nullable();
            $table->timestamp('resuelta_en')->nullable();
            $table->unsignedBigInteger('resuelta_por')->nullable();
            $table->unsignedInteger('monto_devuelto')->default(0);
            $table->unsignedInteger('monto_retenido')->default(0);
            $table->text('motivo_retencion')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('ingresos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reserva_id');
            $table->dropConstrainedForeignId('garantia_id');
        });
        Schema::table('garantias', function (Blueprint $table) {
            $table->dropColumn([
                'registrada_en', 'registrada_por', 'fecha_pago', 'medio_pago', 'talonario',
                'fecha_resolucion', 'resuelta_en', 'resuelta_por', 'monto_devuelto', 'monto_retenido', 'motivo_retencion',
            ]);
        });
    }
};
