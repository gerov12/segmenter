<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class OperativosPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $arrayOfPermissionNames = ['Ver Operativos', 'Administrar Operativos'];

        $permissions = collect($arrayOfPermissionNames)->map(function ($permission) {
            return ['name' => $permission, 'guard_name' => 'web'];
        });

        foreach ($permissions as $permission) {
            $this->command->info('Creando permiso '.$permission['name']);
            try{
                Permission::firstOrcreate($permission);
                $this->command->info('Permiso '.$permission['name'].' creado.');
            } catch ( Spatie\Permission\Exception $e) {
                $this->command->error('Error creando permiso '.$permission['name'].'...');
                echo __($e->getMessage());
            }
        }
    }
}
