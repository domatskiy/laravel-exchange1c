<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create1cProductPropertyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('1c_tmp_product_property', function (Blueprint $table) {
            $table->increments('id');
            $table->string('catalog_id', 150);
            $table->string('xml_id', 50);
            $table->string('name', 200);
            $table->string('type', 1);
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
        Schema::drop('1c_tmp_product_property');
    }
}
