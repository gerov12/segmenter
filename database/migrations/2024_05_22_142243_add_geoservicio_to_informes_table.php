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
        Schema::table('informes', function (Blueprint $table) {
            $table->unsignedBigInteger('geoservicio_id')->nullable()->after('user_id'); // Agregar la columna 'geoservicio_id'
            $table->foreign('geoservicio_id')->references('id')->on('geoservicios')->onDelete('set null'); // Agregar la relación
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('informes', function (Blueprint $table) {
            //
        });
    }
};
