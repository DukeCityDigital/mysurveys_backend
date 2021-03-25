<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDemographicitemsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('demographicitems', function (Blueprint $table) {
            $table->id();
            $table->string("dc_fieldname");
            $table->integer("itemorder")->null();
            $table->timestamps();
            //key
            //            $table->foreign('dc_fieldname')->references('fieldname')->on('demographiccategories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('demographicitems');
    }
}
