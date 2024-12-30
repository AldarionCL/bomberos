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
        Schema::create('gastos_tipo', function (Blueprint $table) {
            $table->id();
            $table->string('Tipo');
            $table->string('Descripcion')->nullable();
            $table->integer('Activo');

            $table->timestamps();
        });

        Schema::table('gastos', function (Blueprint $table) {
            $table->foreign('TipoGasto')->references('id')->on('gastos_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_tipo');
    }
};
