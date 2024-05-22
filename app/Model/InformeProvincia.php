<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Model\Provincia;

class InformeProvincia extends Model
{
    use HasFactory;

    protected $table = 'informe_provincia';

    public function provincia()
    {
        return $this->belongsTo(Provincia::class);
    }

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
