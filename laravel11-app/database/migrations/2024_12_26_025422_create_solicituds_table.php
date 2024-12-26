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
        Schema::create('solicituds', function (Blueprint $table) {
            $table->id();
            $table->integer('tipoSolicitud');
            $table->integer('estado')->default(1);
            $table->dateTime('fecha_registro');
            $table->integer('solicitadoPor');

            $table->string('nombrePostulante');
            $table->string('telefonoPostulante');
            $table->string('correoPostulante');
            $table->string('direccionPostulante');
            $table->string('observaciones');
            $table->string('nivelEstudioPostulante');
            $table->date('fechaNacimientoPostulante');
            $table->string('sexoPostulante');
            $table->string('estadoCivilPostulante');
            $table->string('ocupacionPostulante');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds');
    }
};
