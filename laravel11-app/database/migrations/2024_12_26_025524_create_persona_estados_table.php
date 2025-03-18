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
        Schema::create('persona_estados', function (Blueprint $table) {
            $table->id();
            $table->string('Estado');
            $table->string('Descripcion')->nullable();
            $table->boolean('Locked')->default(false);

            $table->timestamps();
        });

        Schema::table('personas', function (Blueprint $table) {
            $table->foreign('idEstado')->references('id')->on('persona_estados');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('persona_estados');
    }
};
