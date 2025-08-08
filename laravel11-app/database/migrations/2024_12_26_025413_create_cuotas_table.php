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
        Schema::create('cuotas', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('idUser')->unsigned();
            $table->dateTime('FechaPeriodo')->nullable();
            $table->dateTime('FechaVencimiento')->nullable();
            $table->dateTime('FechaPago')->nullable();
            $table->bigInteger('Estado')->unsigned();
            $table->integer('Monto');
            $table->integer('Pendiente')->nullable();
            $table->integer('Recaudado')->nullable();
            $table->integer('SaldoFavor')->nullable();
            $table->string('Documento')->nullable();
            $table->string('DocumentoArchivo')->nullable();
            $table->string('TipoCuota', 50)->nullable();
            $table->bigInteger('AprobadoPor')->unsigned();

            $table->foreign('idUser')->references('id')->on('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas');
    }
};
