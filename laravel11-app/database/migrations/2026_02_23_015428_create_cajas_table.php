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
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->integer('monto');
            $table->integer('impuesto')->nullable();
            $table->integer('total');
            $table->unsignedBigInteger('id_usuario');
            $table->string('descripcion')->nullable();
            $table->string('tipo')->comment('ingreso o egreso');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caja');
    }
};
