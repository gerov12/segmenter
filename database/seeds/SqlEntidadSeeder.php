<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SqlEntidadSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $this->command->info('Sembrando entidades...');
        $path = 'app/developer_docs/entidad.sql';
        try{
            DB::unprepared(file_get_contents($path));
            }catch(Exception $e){
                if ($e->getCode()==23505){
                    $this->command->error('Entidades NO fueron plantadas!');
                    return 0;
                }
            }
        $this->command->info('Entidades plantadas!');
    }
}
