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
            $table->integer('idUsuario')->unsigned();
            $table->integer('idRole')->unsigned();
            $table->integer('idCargo')->unsigned();
            $table->integer('idEstado')->unsigned();
            $table->string('Rut');
            $table->string('Telefono')->nullable();
            $table->string('Direccion')->nullable();
            $table->date('FechaNacimiento')->nullable();
            $table->date('FechaReclutamiento')->nullable();
            $table->string('Sexo')->nullable();
            $table->string('EstadoCivil')->nullable();
            $table->string('Ocupacion')->nullable();
            $table->boolean('Activo');

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
