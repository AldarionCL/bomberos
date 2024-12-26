<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('solicitudes', function (Blueprint $table) {
            $table->id();
            $table->integer('TipoSolicitud');
            $table->integer('Estado')->default(1);
            $table->dateTime('Fecha_registro');
            $table->integer('SolicitadoPor');

            $table->string('NombrePostulante');
            $table->string('TelefonoPostulante');
            $table->string('CorreoPostulante');
            $table->string('DireccionPostulante');
            $table->string('Observaciones');
            $table->string('NivelEstudioPostulante');
            $table->date('FechaNacimientoPostulante');
            $table->string('SexoPostulante');
            $table->string('EstadoCivilPostulante');
            $table->string('OcupacionPostulante');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicitudes');
    }
};
