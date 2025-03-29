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
            $table->bigInteger('AsociadoA')->unsigned();
            $table->Text('Observaciones')->nullable();

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
