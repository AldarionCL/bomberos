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
        Schema::create('precio_cuotas', function (Blueprint $table) {
            $table->id();
            $table->string('TipoVoluntario');
            $table->string('TipoCuota');
            $table->integer('Monto')->default(0);
            $table->date('periodo')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('precio_cuotas');
    }
};
