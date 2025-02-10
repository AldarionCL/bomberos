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
        Schema::create('documentos_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('Tipo');
            $table->string('Descripcion')->nullable();

            $table->timestamps();
        });

        Schema::table('documentos', function (Blueprint $table) {
            $table->foreign('TipoDocumento')->references('id')->on('documentos_tipos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_tipos');
    }
};
