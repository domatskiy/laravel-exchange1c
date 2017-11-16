<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Create1cSectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('1c_tmp_sections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('catalog_id', 150);
            $table->string('name', 150);
            $table->integer('parent_id')->unsigned()->nullable()->default(null)->comment('Родитель');
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
        Schema::drop('1c_tmp_sections');
    }
}
