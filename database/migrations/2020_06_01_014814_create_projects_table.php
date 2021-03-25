<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string("name")->nullable();
            $table->integer("idprojectclassification")->nullable();
            $table->integer("creator_userid")->nullable();
            $table->enum('state', ['Design', 'Started', 'Halted'])->default("Design");
            $table->enum('start_state', ['Open', 'Closed'])->default("Open");
            $table->text("link")->nullable();
            $table->text("refcode")->nullable();
            $table->string("responsible_person")->nullable();
            $table->string("project_title")->nullable();
            $table->text("description")->nullable();
            $table->datetime("defaultstart")->nullable();
            $table->datetime("defaultend")->nullable();
            $table->text("basicselection")->nullable();
            $table->text("advancedselection")->nullable();
            $table->enum('currentselection', ['Basic', 'Advanced'])->nullable();
            $table->enum('payout_type', ['Fixed', 'Variable'])->default("Fixed");
            $table->decimal('quota', 10, 2)->default(0.00);
            $table->integer("desired_sample_size")->nullable();
            $table->integer("desired_num_invitations")->nullable();
            $table->integer("max_participants")->nullable();
            $table->integer("max_invitations")->nullable();
            $table->decimal('max_payout', 10, 2)->nullable()->default("0");
            $table->decimal('exp_payout', 10, 2)->nullable();
            $table->integer("exp_max_participants")->nullable();
            $table->integer("exp_max_invitations")->nullable();
            $table->binary('tempupload')->nullable();
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
        Schema::dropIfExists('projects');
    }
}
