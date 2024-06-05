<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;

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
            '--class' => 'InformesPermissionsSeeder',
            '--force' => true ]
        );
        Artisan::call( 'db:seed', [
            '--class' => 'GeoserviciosPermissionsSeeder',
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
            try {
                /** elimino los permisos de administración de informes*/
                Permission::where(['name'=>'Generar Informes'])->first()->delete();
                Permission::where(['name'=>'Ver Informes'])->first()->delete();
                Permission::where(['name'=>'Importar Geometrias'])->first()->delete();

                /** elimino los permisos de administración de geoservicios*/
                Permission::where(['name'=>'Administrar Geoservicios'])->first()->delete();

            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                echo $e->getMessage();
            }
            

            DB::commit();
    }
};
