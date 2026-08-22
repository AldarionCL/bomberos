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
        Schema::table('cuotas_tipo', function (Blueprint $table) {
            $table->boolean('es_mensual')->default(false)->after('tipoCobro');
        });

        // El tipo de cuota que actualmente tiene un precio configurado y se ha
        // venido generando mes a mes es, en la práctica, la cuota mensual del club.
        // Se marca automáticamente para no perder la configuración existente.
        $nombreTipoMensual = DB::table('precio_cuotas')
            ->orderByDesc('id')
            ->value('TipoCuota');

        if ($nombreTipoMensual) {
            DB::table('cuotas_tipo')
                ->where('nombre', $nombreTipoMensual)
                ->update(['es_mensual' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas_tipo', function (Blueprint $table) {
            $table->dropColumn('es_mensual');
        });
    }
};
