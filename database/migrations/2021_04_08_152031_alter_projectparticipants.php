<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterProjectparticipants extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('project_participant', function (Blueprint $table) {
            //
            $table->string("paymentorders_payorderid")->change();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('project_participant', function (Blueprint $table) {
            //
            $table->dropColumn("paymentorders_payorderid")->nullable();

        });
    }
}
