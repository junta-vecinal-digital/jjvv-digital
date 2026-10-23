<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservas', fn (Blueprint $table) => $table->string('numero_transaccion', 50)->nullable());
        foreach ([
            'transferencia_banco' => ['Banco', 'Banco para transferencias'],
            'transferencia_titular' => ['JJVV', 'Titular de la cuenta'],
            'transferencia_cuenta' => ['123546', 'Cuenta bancaria (tipo y número)'],
            'transferencia_rut' => ['11.111.111-1', 'RUT del titular de la cuenta'],
            'transferencia_email' => ['jjvv@jjvv.cl', 'Correo para transferencias'],
        ] as $clave => [$valor, $etiqueta]) {
            DB::table('parametros')->insert(['clave' => $clave, 'valor' => $valor, 'etiqueta' => $etiqueta, 'created_at' => now(), 'updated_at' => now()]);
        }
    }

    public function down(): void
    {
        Schema::table('reservas', fn (Blueprint $table) => $table->dropColumn('numero_transaccion'));
        DB::table('parametros')->whereIn('clave', ['transferencia_banco', 'transferencia_titular', 'transferencia_cuenta', 'transferencia_rut', 'transferencia_email'])->delete();
    }
};
