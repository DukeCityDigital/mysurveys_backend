<?php

use Illuminate\Database\Seeder;
use App\Settings;

class SettingsSeeder extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $sData = [
            "paypal_api_endpoint" => '**',
            "paypal_api_version" => '2.3',
            "paypal_key_hash" => sha1('sandboxsandbox'),
            "paypal_key_currency" => 'USD',
            "default_replyto" => 'support@MySurveys.santafe.edu',
            "redirection_email" => 'support@MySurveys.santafe.edu',
            "contactaddress" => 'support@MySurveys.santafe.edu',
            "logo_filename" => "img/sfilogo.jpg",
            "logo_description" => "MySurveys",
            "webpanelname" => "MySurveys",
            "divisionlogo_filename" => "img/sfilogo.jpg",
            "divisionlogo_description" => "MySurveys",
            "design" => "MPIB",
            "preloginmessage" => "Initial Pre-login message",
            "participantmessage" => "Initial Participant message",
            "researchermessage" => "Initial Researcher message",
            "created_at" =>  \Carbon\Carbon::now(), # new \Datetime()
            "updated_at" => \Carbon\Carbon::now(),  # new \Datetime()
        ];



        Settings::insert($sData);
    }
}
