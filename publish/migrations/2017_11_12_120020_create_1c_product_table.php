<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create1cProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('1c_tmp_product', function (Blueprint $table) {
            $table->increments('id');
            $table->string('catalog_id', 150);
            $table->string('xml_id', 50);
            $table->string('name');
            $table->integer('section_id')->unsigned()->nullable()->default(null)->comment('Раздел');
            $table->timestamps();
            });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('1c_tmp_product');
    }
}
