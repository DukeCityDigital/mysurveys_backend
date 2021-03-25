<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendlistTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendlist', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("projects_projectid")->null();
            $table->text("mailto");
            $table->text("subject");
            $table->text("body");
            $table->text("replyto");
            $table->datetime("lastattempt")->null();
            $table->timestamps();
            $table->foreign('projects_projectid')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sendlist');
    }
}
