<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;
use App\Model\Operativo;

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
        'geoservicio_id', //null para los geoservicios temporales (conexión rápida)
        'geoservicio_url', //solo para las conexiones rapidas ya que no hay id de geoservicio
        'geoservicio_nombre' //solo para las conexiones rapidas ya que no hay id de geoservicio
    ];

    public function operativo()
    {
        return $this->belongsTo(Operativo::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function geoservicio()
    {
        return $this->belongsTo(Geoservicio::class, 'geoservicio_id');
    }

    public function provincias() {
        return $this->belongsToMany(Provincia::class, 'informe_provincia')
            ->using(InformeProvincia::class)
            ->withPivot('existe_cod', 'existe_nom', 'estado', 'estado_geom', 'errores', 'cod', 'nom')
            ->withTimestamps();
    }
}
