<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Operativo extends Model
{
    //
    protected $table='operativo';

    /**
     * Relación con Operativo.
     *
     */

     public function provincias(): BelongsToMany
     {
         return $this->BelongsToMany('App\Model\Provincia','operativo_provincia','operativo_id','provincia_id');
     }
}
