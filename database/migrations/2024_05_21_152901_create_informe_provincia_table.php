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
        Schema::create('informe_provincia', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('informe_id');
            $table->unsignedBigInteger('provincia_id')->nullable();
            $table->boolean('existe_cod');
            $table->boolean('existe_nom');
            $table->string('estado');
            $table->string('estado_geom');
            $table->integer('errores');
            $table->string('cod');
            $table->string('nom');
            $table->timestamps();

            $table->foreign('informe_id')->references('id')->on('informes')->onDelete('cascade');
            $table->foreign('provincia_id')->references('id')->on('provincia')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('informe_provincia');
    }
};
