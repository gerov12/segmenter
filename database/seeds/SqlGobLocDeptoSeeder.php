<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SqlGobLocDeptoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $this->command->info('Relacionando Gobiernos Locales con Departamentos ...');
        $path = 'app/developer_docs/gobierno_local_departamento.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('Gobiernos Locales relacionados a Departamentos!');
    }
}
