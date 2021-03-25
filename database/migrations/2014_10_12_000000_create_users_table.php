<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->datetime("registration_date")->nullable();
            $table->datetime("last_login")->nullable();
            $table->datetime("last_update")->nullable();
            $table->tinyInteger("banned")->default(0);
            $table->tinyInteger("warnings")->default(0);
            $table->string("banned_reason")->nullable();
            $table->datetime("banned_date")->nullable();
            $table->datetime("email_verified_at")->nullable();
            $table->string('verification_code')->nullable();
            $table->tinyInteger("activated")->default(0);
            $table->string("registration_key")->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
