<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentordersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('paymentorders', function (Blueprint $table) {
            $table->id();
            $table->datetime("order_date")->null();
            $table->datetime("completion_date")->null();
            $table->text('comment')->null();
            $table->enum('paymenttype', ['Manual', 'Paypal']);
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
        Schema::dropIfExists('paymentorders');
    }
}
