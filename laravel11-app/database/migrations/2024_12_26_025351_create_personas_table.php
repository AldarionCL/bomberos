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
            $table->string('rut');
            $table->string('telefono')->nullable();
            $table->string('direccion')->nullable();
            $table->date('fechaNacimiento')->nullable();
            $table->string('sexo')->nullable();
            $table->string('estadoCivil')->nullable();
            $table->string('ocupacion')->nullable();
            $table->boolean('activo');

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
