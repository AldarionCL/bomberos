<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_sitio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_grupo')->default('Club');
            $table->string('logo')->nullable();
            $table->string('color')->default('#023aab');
            $table->timestamps();
        });

        // Fila única (singleton): siempre id = 1.
        DB::table('configuracion_sitio')->insert([
            'nombre_grupo' => config('app.name', 'Club'),
            'color' => '#023aab',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_sitio');
    }
};
