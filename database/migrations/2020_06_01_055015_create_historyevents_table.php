<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryeventsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('historyevents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("projects_projectid")->null();
            $table->datetime("event")->null();
            $table->text("historytext");
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
        Schema::dropIfExists('historyevents');
    }
}
