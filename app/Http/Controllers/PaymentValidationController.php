<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\ProjectParticipant;
use Carbon\Carbon;


class PaymentValidationController extends BaseController
{

    /**
     * Change users PP validation status
     * @param Request $request
     */
    public function update_participant_validation(Request $request)
    {

        $submit = $request->all();

        // var_dump($submit);
        // exit;
        foreach ($submit as $s) {
            // var_dump($s);
            $pp = ProjectParticipant::with(['participant'])->where("projects_projectid", $s['project_id'])
                ->where("participants_userid", $s['participants_userid'])
                ->first();
            // echo('havepp');
            if ($pp) {
                
                $pp->participant->valid = $s['valid'];
                $pp->participant->group = $s['group'];
                $pp->participant->save();
                $pp->amount_to_pay = $s['amount_to_pay'];
                $pp->paymentorders_payorderid = $s['paymentorders_payorderid'];
                if (isset($s['payment_confirmed'])) {
                    $pp->payment_confirmed = date("Y-m-d H:i:s");
                }
                $dt = date("Y-m-d H:i:s");
                $pp->validated = $dt;
                $pp->save();
            }
          
        }
        return $this->sendResponse(count($submit) . " users updated", "updated");
    }
}
