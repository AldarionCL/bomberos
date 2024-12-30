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
            $table->date('FechaNacimiento')->nullable();
            $table->date('FechaReclutamiento')->nullable();
            $table->string('Sexo')->nullable();
            $table->string('EstadoCivil')->nullable();
            $table->string('Ocupacion')->nullable();
            $table->boolean('Activo');

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
