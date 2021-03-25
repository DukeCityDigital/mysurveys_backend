<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantdemographicsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participantdemographics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("participants_userid");
            $table->string("di_fieldname")->null();
            $table->string("di_valuename")->null();
            $table->timestamps();
            $table->foreign('participants_userid')->references('user_id')->on('participants');
            //            $table->foreign('dc_fieldname')->references('dc_fieldname')->on('demographicitems');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participantdemographics');
    }
}
