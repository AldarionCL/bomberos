<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionSitio extends Model
{
    protected $table = 'configuracion_sitio';

    protected $fillable = [
        'nombre_grupo',
        'logo',
        'color',
    ];

    public static function actual(): self
    {
        return self::firstOrCreate(['id' => 1]);
    }
}
