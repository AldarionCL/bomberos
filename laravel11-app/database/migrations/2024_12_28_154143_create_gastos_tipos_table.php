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
        Schema::create('gastos_tipos', function (Blueprint $table) {
            $table->id();
            $table->string('Tipo');
            $table->string('Proveedor');
            $table->integer('MontoNeto');
            $table->integer('MontoIva');
            $table->integer('MontoTotal');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gastos_tipos');
    }
};
