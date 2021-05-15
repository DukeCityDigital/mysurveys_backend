<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterParticipantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('participants', function (Blueprint $table) {
            //
            $table->tinyInteger('vac_benefit')->nullable();
            $table->tinyInteger('vac_effective')->nullable();
            $table->tinyInteger('vac_harmful')->nullable();
            $table->tinyInteger('vac_pharma')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('participants', function (Blueprint $table) {
            //
            $table->dropColumn('vac_benefit');
            $table->dropColumn('vac_effective');
            $table->dropColumn('vac_harmful');
            $table->dropColumn('vac_pharma');
        });
    }
}
