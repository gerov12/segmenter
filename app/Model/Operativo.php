<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Operativo extends Model
{
    //
    protected $table='operativo';

    protected $fillable = [
        'nombre',
        'observacion'
    ];

    public $timestamps = false; //ya que la tabla no tiene timestamps y el create da error

    /**
     * Relación con Operativo.
     *
     */

     public function provincias(): BelongsToMany
     {
         return $this->BelongsToMany('App\Model\Provincia','operativo_provincia','operativo_id','provincia_id');
     }
}
