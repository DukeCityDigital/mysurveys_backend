<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectusersTable extends Migration
{

    /**
     * Run the migrations.
     * TODO may factor out re: obsolete
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("project_id");
            $table->unsignedBigInteger("researcher_id");

            $table->timestamps();

            //            $table->foreign('project_id')->references('id')->on('projects');
            //            $table->foreign('researcher_id')->references('user_id')->on('researchers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_users');
    }
}
