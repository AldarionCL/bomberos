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
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->string('FotoPostulante')->nullable();
            $table->integer('EdadPostulante')->nullable();
            $table->string('NacionalidadPostulante')->nullable();
            $table->string('SituacionMilitarPostulante')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn('FotoPostulante');
            $table->dropColumn('EdadPostulante');
            $table->dropColumn('NacionalidadPostulante');
            $table->dropColumn('SituacionMilitarPostulante');
        });
    }
};
