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
        Schema::create('informes', function (Blueprint $table) {
            $table->id();
            // se agrega relación con geoservicio en la migration de geoservicios
            $table->string('capa');
            $table->string('tabla');
            $table->integer('elementos_erroneos');
            $table->integer('total_errores');
            $table->string('cod');
            $table->string('nom');
            $table->unsignedBigInteger('operativo_id')->nullable();
            $table->timestamp('datetime');
            $table->unsignedBigInteger('user_id');
            $table->timestamps();

            $table->foreign('operativo_id')->references('id')->on('operativo');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informes');
    }
};
