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
        Schema::create('noticias', function (Blueprint $table) {
            $table->id();
            $table->string('Titulo');
            $table->longText('Subtitulo')->nullable();
            $table->longText('Contenido');
            $table->longText('Imagen')->nullable();
            $table->boolean('Estado')->default(true);
            $table->dateTime('FechaPublicacion');
            $table->dateTime('FechaExpiracion')->nullable();
            $table->bigInteger('createdBy')->unsigned();

            $table->foreign('createdBy')->references('id')->on('users');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('noticias');
    }
};
