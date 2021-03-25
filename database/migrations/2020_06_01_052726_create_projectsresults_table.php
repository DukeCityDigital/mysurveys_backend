<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsresultsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projectsresults', function (Blueprint $table) {
            $table->unsignedBigInteger("participants_userid");
            $table->string("resultsfields_fieldname");
            $table->string("fieldvalue")->null();
            $table->timestamps();
            $table->foreign('participants_userid')->references('user_id')->on('participants');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projectsresults');
    }
}
