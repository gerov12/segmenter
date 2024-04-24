<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTipoDePoblacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
  If (! Schema::hasTable('tipo_de_poblacion')){
	Schema::create('tipo_de_poblacion', function (Blueprint $table) {
		$table->bigIncrements('id')->index();
		$table->string('nombre')->index();
		$table->string('descripcion')->nullable();
		$table->timestamps();
   });
   }else{
	  echo __('Omitiendo creación de tabla de tipo_de_poblacion existente...
		  ');
   }
}
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tipo_de_poblacion');
    }
}
