<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            // Los registros anteriores conservan sus condiciones de pago y garantía.
            $table->boolean('aporte_variable')->default(false);
            $table->foreignId('aporte_definido_por')->nullable()->constrained('users');
            $table->timestamp('aporte_definido_en')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservas', function (Blueprint $table) {
            $table->dropConstrainedForeignId('aporte_definido_por');
            $table->dropColumn(['aporte_variable', 'aporte_definido_en']);
        });
    }
};
