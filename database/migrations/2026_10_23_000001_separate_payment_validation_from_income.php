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
            $table->timestamp('pago_validado_en')->nullable();
            $table->foreignId('pago_validado_por')->nullable()->constrained('users')->nullOnDelete();
        });
        DB::table('solicitudes_insumos')->whereNotNull('pago_registrado_en')->update([
            'pago_validado_en' => DB::raw('pago_registrado_en'),
            'pago_validado_por' => DB::raw('pago_registrado_por'),
        ]);
    }

    public function down(): void
    {
        Schema::table('solicitudes_insumos', function (Blueprint $table) {
            $table->dropForeign(['pago_validado_por']);
            $table->dropColumn(['pago_validado_en', 'pago_validado_por']);
        });
    }
};
