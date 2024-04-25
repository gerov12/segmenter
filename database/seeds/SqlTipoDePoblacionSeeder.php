<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SqlTipoDePoblacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        try{
          $this->command->info('tipo_de_poblacion table seed!');
          $path = 'app/developer_docs/tipo_de_poblacion.sql';
          DB::unprepared(file_get_contents($path));
        }catch(QueryException $e){
             if ($e->getCode()==23505){
                 $this->command->error('Tipo de Poblacion NO fueron plantados (ya existían)!');
                 return 0;
            }
        }
        $this->command->info('Tipo de Poblacion table seeded!');
    }
}
