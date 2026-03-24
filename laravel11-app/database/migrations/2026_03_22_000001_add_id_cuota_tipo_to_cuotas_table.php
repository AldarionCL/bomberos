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
        Schema::table('cuotas', function (Blueprint $table) {
            $table->unsignedBigInteger('idCuotaTipo')->nullable()->after('id');
            $table->foreign('idCuotaTipo')->references('id')->on('cuotas_tipo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas', function (Blueprint $table) {
            $table->dropForeign(['idCuotaTipo']);
            $table->dropColumn('idCuotaTipo');
        });
    }
};
