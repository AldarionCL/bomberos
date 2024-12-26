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
            $table->dateTime('fechaPeriodo')->nullable();
            $table->dateTime('fechaVencimiento')->nullable();
            $table->dateTime('fechaPago')->nullable();
            $table->integer('estado');
            $table->string('documento')->nullable();
            $table->string('documentoArchivo')->nullable();
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
