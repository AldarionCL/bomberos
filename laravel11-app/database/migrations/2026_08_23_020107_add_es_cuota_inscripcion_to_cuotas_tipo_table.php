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
            $table->boolean('es_cuota_inscripcion')->default(false)->after('es_mensual');
        });

        // "Cuota Inscripción Club" quedó sin cuotas asociadas tras migrar su uso
        // histórico (mal empleado como la cuota mensual) a "Cuota Mensual". Es el
        // candidato natural para volver a cumplir su nombre: el cobro único al
        // ingresar un socio.
        DB::table('cuotas_tipo')
            ->where('nombre', 'Cuota Inscripción Club')
            ->update(['es_cuota_inscripcion' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cuotas_tipo', function (Blueprint $table) {
            $table->dropColumn('es_cuota_inscripcion');
        });
    }
};
