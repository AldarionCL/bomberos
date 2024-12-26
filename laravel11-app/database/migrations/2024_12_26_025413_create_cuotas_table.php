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
            $table->integer('idUser')->unsigned();
            $table->dateTime('FechaPeriodo')->nullable();
            $table->dateTime('FechaVencimiento')->nullable();
            $table->dateTime('FechaPago')->nullable();
            $table->integer('Estado');
            $table->string('Documento')->nullable();
            $table->string('DocumentoArchivo')->nullable();
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
