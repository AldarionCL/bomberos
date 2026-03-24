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
        Schema::table('cuotas_tipo', function (Blueprint $table) {
            $table->string('tipoCobro')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas_tipo', function (Blueprint $table) {
            $table->dropColumn('tipoCobro');
        });
    }
};
