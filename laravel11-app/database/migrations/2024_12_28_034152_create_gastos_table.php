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
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();
            $table->string('Descripcion')->nullable();
            $table->date('FechaGasto');
            $table->integer('MontoGasto');
            $table->integer('MontoIva')->default(0);
            $table->integer('MontoTotal');
            $table->string('TipoGasto');
            $table->unsignedBigInteger('AsociadoA')->nullable();
            $table->unsignedBigInteger('idCaja')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos');
    }
};
