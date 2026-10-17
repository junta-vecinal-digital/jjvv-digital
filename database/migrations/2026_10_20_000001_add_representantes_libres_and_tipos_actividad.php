<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grupos', function (Blueprint $table) {
            $table->foreignId('representante_usuario_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('representante_nombre')->nullable();
            $table->string('representante_correo')->nullable();
            $table->string('representante_telefono', 20)->nullable();
            $table->string('representante_direccion')->nullable();
        });
        foreach (DB::table('grupos')->join('socios', 'socios.id', '=', 'grupos.representante_id')
            ->select('grupos.id', 'socios.nombre_completo', 'socios.email', 'socios.telefono', 'socios.direccion', 'socios.user_id')->get() as $grupo) {
            DB::table('grupos')->where('id', $grupo->id)->update([
                'representante_usuario_id' => $grupo->user_id,
                'representante_nombre' => $grupo->nombre_completo,
                'representante_correo' => $grupo->email,
                'representante_telefono' => $grupo->telefono,
                'representante_direccion' => $grupo->direccion,
            ]);
        }

        Schema::create('tipos_actividad', function (Blueprint $table) {
            $table->id();
            $table->string('clave', 20)->unique();
            $table->string('nombre', 120)->unique();
            $table->boolean('activo')->default(true);
            $table->timestamps();
        });
        $tipos = ['grupo' => 'Actividad de grupo', 'particular' => 'Actividad particular', 'actividad' => 'Actividad de la junta'];
        foreach ($tipos as $clave => $nombre) {
            DB::table('tipos_actividad')->insert([
                'clave' => $clave, 'nombre' => $nombre, 'activo' => true, 'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        Schema::table('reservas', function (Blueprint $table) {
            $table->string('tipo_nombre', 120)->nullable();
            $table->string('responsable_nombre')->nullable();
        });
        foreach (DB::table('reservas')->leftJoin('socios', 'socios.id', '=', 'reservas.socio_id')
            ->leftJoin('users', 'users.id', '=', 'reservas.creado_por')
            ->select('reservas.id', 'reservas.tipo', 'socios.nombre_completo', 'users.name')->get() as $reserva) {
            DB::table('reservas')->where('id', $reserva->id)->update([
                'tipo_nombre' => $tipos[$reserva->tipo] ?? ($reserva->tipo === 'arriendo' ? 'Arriendo' : null),
                'responsable_nombre' => $reserva->nombre_completo ?? ($reserva->tipo === 'actividad' ? $reserva->name : null),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('reservas', fn (Blueprint $table) => $table->dropColumn(['tipo_nombre', 'responsable_nombre']));
        Schema::dropIfExists('tipos_actividad');
        Schema::table('grupos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('representante_usuario_id');
            $table->dropColumn(['representante_nombre', 'representante_correo', 'representante_telefono', 'representante_direccion']);
        });
    }
};
