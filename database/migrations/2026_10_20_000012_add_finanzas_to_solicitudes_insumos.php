<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_insumos', function (Blueprint $table) {
            $table->timestamp('pago_registrado_en')->nullable();
            $table->foreignId('pago_registrado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->date('fecha_pago')->nullable();
            $table->string('medio_pago', 20)->nullable();
            $table->string('talonario', 30)->nullable();
            $table->string('numero_transaccion', 50)->nullable();
            $table->string('comprobante_path')->nullable();
        });
        Schema::table('ingresos', function (Blueprint $table) {
            $table->foreignId('solicitud_insumo_id')->nullable()->constrained('solicitudes_insumos');
        });
        Schema::table('garantias', function (Blueprint $table) {
            $table->foreignId('reserva_id')->nullable()->change();
            $table->foreignId('solicitud_insumo_id')->nullable()->unique()->constrained('solicitudes_insumos');
        });
    }

    public function down(): void
    {
        if (DB::table('solicitudes_insumos')->whereNotNull('pago_registrado_en')->exists()
            || DB::table('ingresos')->whereNotNull('solicitud_insumo_id')->exists()
            || DB::table('garantias')->whereNotNull('solicitud_insumo_id')->exists()) {
            throw new RuntimeException('No se puede revertir esta migración con registros financieros de insumos: se perdería su trazabilidad.');
        }
        Schema::table('garantias', function (Blueprint $table) {
            $table->dropConstrainedForeignId('solicitud_insumo_id');
            $table->foreignId('reserva_id')->nullable(false)->change();
        });
        Schema::table('ingresos', fn (Blueprint $table) => $table->dropConstrainedForeignId('solicitud_insumo_id'));
        Schema::table('solicitudes_insumos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pago_registrado_por');
            $table->dropColumn(['pago_registrado_en', 'fecha_pago', 'medio_pago', 'talonario', 'numero_transaccion', 'comprobante_path']);
        });
    }
};
