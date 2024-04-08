<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OperativoProv extends Model
{
    //
    protected $table='operativo_provincia';


    /**
     * Relación con Provincias, un Operativo puede estar en varias provincias.
     *
     */

    public function provincias()
    {
        return $this->hasMany('App\Model\Provincia','provincia_id');
    }

    /**
     * Relación con Operativo.
     *
     */

     public function operativo(): hasMany
     {
         return $this->hasMany('App\Model\Operativo','operativo_id');
     }
}
