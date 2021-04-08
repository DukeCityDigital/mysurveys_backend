<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProjectparticipantsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_participant', function (Blueprint $table) {
            $table->unsignedBigInteger("participants_userid");
            $table->unsignedBigInteger("projects_projectid");
            $table->datetime("invited")->nullable();
            $table->datetime("started")->nullable();
            $table->datetime("finished")->nullable();
            $table->decimal('amount_to_pay', 10, 2)->default(0.00);
            $table->datetime("validated")->nullable();
            $table->string("paymentorders_payorderid")->nullable();
            $table->datetime("payment_confirmed")->nullable();
            $table->string("userparam1")->nullable();
            $table->string("userparam2")->nullable();
            $table->string("safeid")->nullable();
            $table->string("started_ip")->nullable();
            $table->string("finished_ip")->nullable();
            $table->timestamps();
            $table->foreign('participants_userid')->references('user_id')->on('participants');
            $table->foreign('projects_projectid')->references('id')->on('projects');
            $table->unique(['participants_userid', 'projects_projectid'], 'ppid_unique');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_participant');
    }
}
