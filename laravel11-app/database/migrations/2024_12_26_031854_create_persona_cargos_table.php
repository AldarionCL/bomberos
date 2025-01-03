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
        Schema::create('persona_cargos', function (Blueprint $table) {
            $table->id();
            $table->string('Cargo');
            $table->string('Descripcion')->nullable();
            $table->integer('Activo')->default(1);

            $table->timestamps();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->foreign('idCargo')->references('id')->on('persona_cargos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_cargos');
    }
};
