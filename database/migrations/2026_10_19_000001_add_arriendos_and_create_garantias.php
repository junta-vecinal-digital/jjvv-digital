<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->unsignedInteger('valor_arriendo')->nullable();
            $table->unsignedInteger('valor_garantia')->nullable();
            $table->string('comprobante_arriendo_path')->nullable();
            $table->timestamp('vence_en')->nullable()->index();
            $table->timestamp('validado_en')->nullable();
            $table->unsignedBigInteger('validado_por')->nullable();
            $table->timestamp('cancelada_en')->nullable();
            $table->unsignedBigInteger('cancelada_por')->nullable();
        });

        Schema::create('garantias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reserva_id')->unique()->constrained('reservas');
            $table->unsignedInteger('monto');
            $table->string('estado', 30)->default('recibida');
            $table->timestamp('recibida_en');
            $table->unsignedBigInteger('validado_por');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('garantias');
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropIndex(['vence_en']);
            $table->dropColumn([
                'valor_arriendo', 'valor_garantia', 'vence_en', 'validado_en',
                'validado_por', 'cancelada_en', 'cancelada_por', 'comprobante_arriendo_path',
            ]);
        });
    }
};
