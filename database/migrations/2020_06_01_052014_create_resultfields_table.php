<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultfieldsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resultfields', function (Blueprint $table) {
            $table->id();
            $table->string("fieldname");
            $table->unsignedBigInteger("projects_projectid");
            $table->timestamps();
            $table->enum('fieldtype', ['Integer', 'Text', 'Number'])->null();
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
        Schema::dropIfExists('resultfields');
    }
}
