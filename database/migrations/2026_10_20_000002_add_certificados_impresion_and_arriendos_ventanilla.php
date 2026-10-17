<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('certificados', fn (Blueprint $table) => $table->boolean('solo_impresion')->default(false));
        Schema::table('reservas', function (Blueprint $table) {
            $table->string('responsable_rut', 12)->nullable();
            $table->string('responsable_direccion')->nullable();
            $table->string('responsable_telefono', 20)->nullable();
            $table->string('canal', 20)->nullable();
        });
        foreach (DB::table('reservas')->join('socios', 'socios.id', '=', 'reservas.socio_id')
            ->where('reservas.tipo', 'arriendo')->select('reservas.id', 'socios.rut', 'socios.direccion', 'socios.telefono')->get() as $reserva) {
            DB::table('reservas')->where('id', $reserva->id)->update([
                'responsable_rut' => $reserva->rut,
                'responsable_direccion' => $reserva->direccion,
                'responsable_telefono' => $reserva->telefono,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('certificados', fn (Blueprint $table) => $table->dropColumn('solo_impresion'));
        Schema::table('reservas', fn (Blueprint $table) => $table->dropColumn(['responsable_rut', 'responsable_direccion', 'responsable_telefono', 'canal']));
    }
};
