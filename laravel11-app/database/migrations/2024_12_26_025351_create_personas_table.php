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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idUsuario')->unsigned();
            $table->bigInteger('idCargo')->unsigned();
            $table->bigInteger('idEstado')->unsigned();
            $table->string('Rut');
            $table->string('Telefono')->nullable();
            $table->string('Direccion')->nullable();
            $table->string('Comuna')->nullable();
            $table->date('FechaNacimiento')->nullable();
            $table->date('FechaReclutamiento')->nullable();
            $table->string('Sexo')->nullable();
            $table->string('EstadoCivil')->nullable();
            $table->string('Ocupacion')->nullable();
            $table->string('Foto')->nullable();
            $table->integer('Edad')->nullable();
            $table->string('Nacionalidad')->nullable();
            $table->string('SituacionMilitar')->nullable();

            $table->string('NivelEstudio')->nullable();
            $table->string('LugarOcupacion')->nullable();
            $table->string('GrupoSanguineo')->nullable();

            $table->string('TallaZapatos')->nullable();
            $table->string('TallaPantalon')->nullable();
            $table->string('TallaCamisa')->nullable();
            $table->string('TallaChaqueta')->nullable();
            $table->string('TallaSombrero')->nullable();

            $table->text('Observaciones')->nullable();

            $table->boolean('Activo')->default(1);

            $table->unique('Rut');
            $table->foreign('idUsuario')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
