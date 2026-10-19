<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados', function (Blueprint $table) {
            $table->string('otorgante_nombre')->nullable();
            $table->string('otorgante_cargo')->nullable();
        });
        Schema::table('ingresos', fn (Blueprint $table) => $table->string('comprobante_path')->nullable());
    }

    public function down(): void
    {
        Schema::table('ingresos', fn (Blueprint $table) => $table->dropColumn('comprobante_path'));
        Schema::table('certificados', fn (Blueprint $table) => $table->dropColumn(['otorgante_nombre', 'otorgante_cargo']));
    }
};
