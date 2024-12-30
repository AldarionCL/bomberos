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
        Schema::create('cuotas_estados', function (Blueprint $table) {
            $table->id();
            $table->string('Estado');
            $table->string('Descripcion')->nullable();
            $table->timestamps();
        });

        Schema::table('cuotas', function (Blueprint $table) {
            $table->foreign('Estado')->references('id')->on('cuotas_estados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cuotas_estados');
    }
};
