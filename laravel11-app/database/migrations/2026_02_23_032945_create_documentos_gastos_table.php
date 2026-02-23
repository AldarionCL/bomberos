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
        Schema::create('documentos_gastos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('idGasto')->unique()->constrained('gastos')->onDelete('cascade');
            $table->string('nombre')->nullable();
            $table->string('ruta_archivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documentos_gastos');
    }
};
