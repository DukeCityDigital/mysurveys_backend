<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\BaseController;
use App\Researcher;
use App\Project;
use App\Helpers\Utilities;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AdminController extends BaseController
{

    /**
     * Add/update quota for project
     * @param type $length
     * @return type
     */
    public function quota(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "project_id" => "required",
            "amount" => "required|numeric"
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->messages()->toArray(), 400);
        }
        $logdata = $validator->valid();
        $logdata['type'] = 'quota';
        $project = Project::findOrFail($request['project_id']);
        $project->quota = $request['amount'];
        $project->save();
        $this->logger('info', "Quota changed to " . $request['amount'], $logdata);
        return $this->sendResponse(["message" => "Quota updated", "project" => $project], 200);
    }

    /**
     * Invite researcher
     */
    public function invite_researcher(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'nickname' => 'required|between:0,15',
        ]);
        if ($validator->fails()) {
            return $this->sendError('Please use a unique nickname and email', 400);
            return response()->json($validator->messages(), 400);
        }

        $unique_nickname = Utilities::checkUniqueNickname($validator->valid()['nickname']);
        $data = $validator->valid();
        $data['password'] = Utilities::generateUUID(12);
        if ($unique_nickname) {
            $rCtrl = new RegisterController();
            if ($newuser = $rCtrl->create_user($data, 'researcher')) {
                $rCtrl->create_participant($newuser->id);
                //send researcher invitation
                $researcher = Researcher::create(
                    [
                        "nickname" => $data['nickname'],
                        "user_id" => $newuser->id,
                    ]
                );
                $this->logger("info", 'User register [researcher]:  .' . $request->all()['email'], $validator->valid());
                $newuser->sendApiEmailVerificationNotification($data);
                return $this->sendResponse($data, 200);
                return response()->json(['user' => $newuser], 201);
            }
        }
        return $this->sendError("User Exists", 500);
    }
}
