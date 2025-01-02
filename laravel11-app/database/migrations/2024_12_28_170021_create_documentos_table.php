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
        Schema::create('documentos', function (Blueprint $table) {
            $table->id();
            $table->string('Nombre');
            $table->string('Descripcion')->nullable();
            $table->string('TipoArchivo')->nullable();
            $table->bigInteger('TipoDocumento')->unsigned();
            $table->integer('Estado')->default(0);
            $table->string('Path')->nullable();
            $table->bigInteger('AsociadoA')->unsigned()->nullable();
            $table->bigInteger('idSolicitud')->unsigned()->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos');
    }
};
