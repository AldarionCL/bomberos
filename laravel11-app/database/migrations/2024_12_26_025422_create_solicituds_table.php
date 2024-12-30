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
            $table->bigInteger('SolicitadoPor')->unsigned();

            $table->string('NombrePostulante')->nullable();
            $table->string('TelefonoPostulante')->nullable();
            $table->string('CorreoPostulante')->nullable();
            $table->string('DireccionPostulante')->nullable();
            $table->string('Observaciones')->nullable();
            $table->string('NivelEstudioPostulante')->nullable();
            $table->date('FechaNacimientoPostulante')->nullable();
            $table->string('SexoPostulante')->nullable();
            $table->string('EstadoCivilPostulante')->nullable();
            $table->string('OcupacionPostulante')->nullable();

            $table->foreign('SolicitadoPor')->references('id')->on('users');

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
