<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\ProjectParticipant;
use Validator;
use App\Http\Controllers\BaseController;
use App\Project;
use App\Participant;
use Illuminate\Support\Facades\Auth;
use App\User;

class MyProjectsController extends BaseController
{

    /**
     * Verify project completion
     * @param Request $request
     * @return type
     */
    public function verify_project_code(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'project_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Missing project code or ID');
        }
        $code = $validator->valid()['code'];

        $pp = ProjectParticipant::where("safeid", $code)->first();
        if (!$pp) {
            return $this->sendResponse(False, 'code not found');
        }
        // $user_id = $request->user()->id;
        // $project_id = $validator->valid()['project_id'];
        $project_actual = Project::find($pp->projects_projectid);

        $max_project_payout = $project_actual->max_payout;

        $user = User::find($pp->participants_userid);

        if (!$pp || !$user) {
            return $this->sendError('No matching user');
        }
        $pp->finished = date("Y-m-d H:i:s");
        $pp->amount_to_pay = $max_project_payout;
        $pp->save();
        if ($token = auth()->login($user)) {
            $aCtrl = new AuthController();
            return $aCtrl->respondWithToken($token, $user);
        }
        return $pp;
    }

    /**
     * Show participant's projects
     */
    public function my_projects(Request $request)
    {

        $user_id = $request->user()->id;
        $user = $request->user();
        $pp = ProjectParticipant::with(['project' => function ($query) {
            $query->where("state", "Started")->where("start_state", "Open");
        }], 'user')
        ->whereNotNull('invited')
        ->where("participants_userid", $user_id)->get();

        $rA = [];

        if ($user->participant->paypal_id_status !== 'Ok') {
            return [];
        }
        if ($user->email_verified_at == NULL) {
            return [];
        }

        foreach ($pp as $p) {
            if ($p->project) {
                $pp = $p->project;
                $link = $this->makeProjectLink($p, $p->project);
                $p->project->link = $link;
                $rA[] = $p;
            }
        }
        return $this->sendResponse($rA, 'Projects retrieved', 200);
    }

    /**
     * Participant Start Project
     * todo check first
     * @param Request $request
     * @return type
     */
    public function start_project(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Missing project ID');
        }
        $project_id = $validator->valid()['project_id'];

        $user_id = Auth()->user()->id;

        $p = Project::state("Started")->startState("Open")->with(['projectParticipants' => function ($query) use ($project_id, $user_id) {
            $query->where('projects_projectid', '=', $project_id);
            $query->where('participants_userid', '=', $user_id);
            $query->whereNotNull('invited');
            $query->whereNull('started'); //participant not started
        }])
            ->where('id', '=', $project_id)
            ->first();


        if (!count($p->projectParticipants)) {
            return $this->sendError('Participant not found', 404);
        }

        $pp = $p->projectParticipants[0];
        //build project link based on user values
        $link = $this->makeProjectLink($pp, $p);
        $pp_actual = ProjectParticipant::where("projects_projectid", $project_id)->where("participants_userid", $user_id)->first();

        $pp_actual->started = date('Y-m-d');

        $pp_actual->save();
        return $this->sendResponse(["user" => $pp_actual, "link" => $link], 'Project started', 200);
    }

    /**
     * Helper - build project link    
     * @param type $pp
     * @param type $p
     * @return type
     */
    public function makeProjectLink($pp, $p)
    {
        if ($pp) {
            $safeid = $pp->safeid;
        } else {
            $safeid = 'testSafeID';
            
        }
        $linkparams = [];
        $linkparams['code'] = $safeid;
        $linkparams['b'] = "";
        $link = $p['link'] . '?' . http_build_query($linkparams);
        return $link;
    }
}
