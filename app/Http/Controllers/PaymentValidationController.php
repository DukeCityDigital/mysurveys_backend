<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\ProjectParticipant;

class PaymentValidationController extends BaseController
{

    /**
     * Change users PP validation status
     * @param Request $request
     */
    public function update_participant_validation(Request $request)
    {

        $submit = $request->all();
        foreach ($submit as $s) {
            $pp = ProjectParticipant::where("projects_projectid", $s['projects_projectid'])
                ->where("participants_userid", $s['participants_userid'])
                ->first();
            $pp->amount_to_pay = $s['amount_to_pay'];
            $pp->paymentorders_payorderid = $s['paymentorders_payorderid'];
            if (isset($s['payment_confirmed'])) {
                $confirmed = date("Y-m-d H:i:s");
                $pp->payment_confirmed = $confirmed;
            }
            $dt = date("Y-m-d H:i:s");
            $pp->validated = $dt;
            $pp->save();
        }
        return $this->sendResponse(count($submit) . " users updated", "updated");
    }
}
