<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    use HasFactory;

    protected $table = 'informes';

    protected $fillable = [
        'capa',
        'tabla',
        'elementos_erroneos',
        'total_errores',
        'cod',
        'nom',
        'operativo_id',
        'datetime',
        'user_id',
    ];

    public function operativo()
    {
        return $this->belongsTo(Operativo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function provincias() {
        return $this->belongsToMany(Provincia::class, 'informe_provincia')
            ->using(InformeProvincia::class)
            ->withPivot('existe_cod', 'existe_nom', 'estado', 'estado_geom', 'errores', 'cod', 'nom')
            ->withTimestamps();
    }
}
