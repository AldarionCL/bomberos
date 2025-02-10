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
        Schema::create('postulantes', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idSolicitud')->unsigned();
            $table->bigInteger('idCargo')->unsigned();

            $table->string('RutPostulante')->nullable();
            $table->string('NombrePostulante')->nullable();
            $table->string('TelefonoPostulante')->nullable();
            $table->string('CorreoPostulante')->nullable();
            $table->string('DireccionPostulante')->nullable();
            $table->string('ComunaPostulante')->nullable();
            $table->string('Observaciones')->nullable();
            $table->string('NivelEstudioPostulante')->nullable();
            $table->date('FechaNacimientoPostulante')->nullable();
            $table->integer('EdadPostulante')->nullable();
            $table->string('SexoPostulante')->nullable();
            $table->string('EstadoCivilPostulante')->nullable();
            $table->string('OcupacionPostulante')->nullable();
            $table->string('FotoPostulante')->nullable();
            $table->string('NacionalidadPostulante')->nullable();
            $table->string('SituacionMilitarPostulante')->nullable();
            $table->string('LugarOcupacionPostulante')->nullable();
            $table->string('GrupoSanguineoPostulante')->nullable();
            $table->string('TallaZapatosPostulante')->nullable();
            $table->string('TallaPantalonPostulante')->nullable();
            $table->string('TallaCamisaPostulante')->nullable();
            $table->string('TallaChaquetaPostulante')->nullable();
            $table->string('TallaSombreroPostulante')->nullable();

            $table->foreign('idSolicitud')->references('id')->on('solicitudes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('postulantes');
    }
};
