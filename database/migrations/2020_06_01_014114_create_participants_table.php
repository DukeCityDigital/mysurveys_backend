<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParticipantsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->unsignedBigInteger("user_id");
            $table->unsignedBigInteger("seed_id")->nullable();
            $table->string("first_name")->nullable();
            $table->string("family_name")->nullable();
            $table->integer("birthyear")->nullable();
            $table->integer("language_id")->nullable();
            $table->tinyInteger("open_for_invitations")->nullable();
            $table->tinyInteger("is_seed")->nullable();
            $table->string("paypal_id")->nullable();
            $table->string("paypal_me")->nullable();
            $table->enum('paypal_id_status', ['New', 'Ok', 'Invalid'])->default('New');
            $table->string("street")->nullable();
            $table->string("zip")->nullable();
            $table->string("city")->nullable();
            $table->tinyInteger("qualification_parents")->nullable();
            $table->tinyInteger("qualification_friends")->nullable();
            $table->tinyInteger("qualification_gm")->nullable();
            $table->tinyInteger("qualification_vac")->nullable();
            $table->tinyInteger("qualification_us")->nullable();
            $table->timestamps();
            $table->unique(['user_id'], 'user_id_unique')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participants');
    }
}
