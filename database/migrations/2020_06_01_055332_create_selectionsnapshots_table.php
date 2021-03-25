<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSelectionsnapshotsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('selectionsnapshots', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("projects_projectid")->null();
            $table->text("safeid")->null();
            $table->text("column_name")->null();
            $table->text("value")->null();
            $table->timestamps();

            //            $table->foreign('projects_projectid')->references('id')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('selectionsnapshots');
    }
}
