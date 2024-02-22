<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Artisan::call( 'db:seed', [
            '--class' => 'RadioPermissionsSeeder',
            '--force' => true ]
          );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::beginTransaction();
            try{
                /** elimino los permisos */
                Permission::where(['name'=>'Eliminar Radios'])->firstOrFail()->delete();
                Permission::where(['name'=>'Modificar Tipo Radios'])->firstOrFail()->delete();
                Permission::where(['name'=>'Desvincular Radios Localidades'])->firstOrFail()->delete();
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                echo $e->getMessage();
            }
            
            DB::commit();
    }
};
