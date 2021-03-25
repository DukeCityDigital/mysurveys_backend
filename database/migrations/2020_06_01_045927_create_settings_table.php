<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->text("paypal_api_endpoint")->nullable();
            $table->text("paypal_api_version")->nullable();
            $table->string("paypal_key_hash")->nullable();
            $table->string("paypal_key_currency")->nullable();
            $table->integer("use_paypal")->nullable();
            $table->integer("use_manual_payment")->nullable();
            $table->integer("require_validation")->default(1);
            $table->integer("test_mode")->default(0);
            $table->integer("block_email")->default(0);
            $table->text("default_replyto")->nullable();
            $table->text("redirection_email")->nullable();
            $table->text("logo_filename")->nullable();
            $table->text("logo_description")->nullable();
            $table->text("webpanelname")->nullable();
            $table->text("webpanel_id")->nullable();
            $table->decimal('minimum_fee', 10, 2)->default(0.50);
            $table->integer("use_linkmethod_direct")->default(0);
            $table->integer("use_linkmethod_login")->default(1);
            $table->integer("use_linkmethod_login_info")->default(1);
            $table->integer("use_linkmethod_unipark")->default(1);
            $table->integer("invited_samplesize_ratio")->default(3);
            $table->text("contactaddress")->nullable();
            $table->text("divisionlogo_filename")->nullable();
            $table->text("divisionlogo_description")->nullable();
            $table->text("design")->nullable();
            $table->text("preloginmessage")->nullable();
            $table->text("adminmessage")->nullable();
            $table->text("participantmessage")->nullable();
            $table->text("researchermessage")->nullable();
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
        Schema::dropIfExists('settings');
    }
}
