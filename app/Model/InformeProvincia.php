<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformeProvincia extends Model
{
    use HasFactory;

    protected $table = 'informe_provincia';

    protected $fillable = [
        'informe_id',
        'provincia_id',
        'existe_cod',
        'existe_nom',
        'estado',
        'estado_geom',
        'errores',
        'cod',
        'nom',
    ];
}
