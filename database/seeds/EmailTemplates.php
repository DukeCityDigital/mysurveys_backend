<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Project;
use App\EmailTemplate;

class EmailTemplates extends Seeder
{

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $eTemplates = [
            [
                "subject" => "Invitation to a MySurveys study",
                "body" => "Hello *username*, *nl**nl* You are invited to join the following study:
                 *nl**nl* *projecttitle*.*nl* *nl* *projectinfo* *nl* *nl*
                 The payment for participation is *maxpayout* USD. The study will be open until *projectenddate*,
                  or until enough people participate.*nl*If you have any questions, please contact *responsibleperson*: 
                  *contactaddress*.*nl* *nl*  ",
                //   TODO loginas 
                //   Please click on the link below to log in to your account or copy and paste
                //    it into your browser. *nl* 
            ],
            [
                "subject" => "MySurveys Reminder",
                "body" => "Hello *username*, *nl**nl* We would like to remind you that you are invited to join
                  the following study: *nl**nl* *projecttitle*. *nl* *nl* *projectinfo*.  *nl* *nl*
                  The payment for participation is *maxpayout* USD. The study will be open until *projectenddate*,
                  or until enough people participate.*nl* If you have any questions, please contact *responsibleperson*:
                  *contactaddress*.*nl* *nl* Please click on the link below to log in to your account, or copy and paste
                   it in your browser.  *nl* ",
            ],
        ];
        foreach ($eTemplates as $eData) {
            EmailTemplate::insert($eData);
        }
    }
}
