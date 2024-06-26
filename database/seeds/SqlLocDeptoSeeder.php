<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SqlLocDeptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $this->command->info('Sembrando relación de localidades y departamentos...');
        $path = 'app/developer_docs/localidad_departamento.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('Relación de Localidades y Departamentos plantadas!');
    }
}
