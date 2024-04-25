<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SqlAgloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('Sembrando aglomerados...');
        $path = 'app/developer_docs/aglomerados.sql';
        DB::unprepared(file_get_contents($path));
        $this->command->info('Aglomerados plantados!');
    }
}
