<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use App\Participant;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\User;
use Validator;
use App\Http\Resources\Participants as ParticipantsResource;
use App\ProjectParticipant;
use Illuminate\Support\Facades\Auth;

class ParticipantController extends BaseController
{

    /**
     * Display a listing of the Participants.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $participants = Participant::where("id", ">", "3")->get();
        return new ProjectParticipants(ProjectParticipant::where("projects_projectid", $request['project_id'])->orderBy($request['sort'], $request['order'])->paginate());
    }

    /**
     * Advanced sel. project participants - rows of conditions
     *
     * @return \Illuminate\Http\Response
     */
    public function get_advanced_selection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Missing project ID');
        }

        $forms = $validator->valid()['categoryForm'];

        $select_paypal_status_ok = $request['paypal_status_ok'] == true;
        $paypal_status = $request['paypal_status'] !== '' && $request['paypal_status'] !== 'Any' ? $request['paypal_status'] : null;

        $survey_complete = $request['survey_complete'] == true;

        $eligible_seed = $request['eligible_seed'] == true;
        $eligible_peers = $request['eligible_peers'] == true;

        $include_seeds = $request['include_seeds'] == 'true';
        $include_peers = $request['include_peers'] == 'true';

        $project_id = $request['project_id'];


        $participants = Participant::with(['friends', 'user'])->whereHas('user', function ($query) use ($project_id, $forms, $select_paypal_status_ok, $paypal_status, $eligible_seed, $eligible_peers, $survey_complete) {
            foreach ($forms as $key => $f) {
                $query->where($f['name'], $f['operator'], $f['value']);
            }
            if ($select_paypal_status_ok) {
                $query->where("paypal_id_status", "=", "Ok");
            }
            if ($survey_complete) {
                $query->whereNotNull("qualification_us");
            }
            if ($eligible_seed) {
                $query->whereHas("friends", function ($query) {
                    $query->where("paypal_id_status", "=", "Ok");
                    // ->pluck("survey_complete");
                    // $query->where("survey_complete", "=", True);
                });

                // $query->where("seed_id", NULL);
            }
            if ($eligible_peers) {
                $query->where("paypal_id_status", "=", "Ok");
                $query->whereNotNull("seed_id");
            }
            if ($paypal_status) {
                $query->where("paypal_id_status", "=", $paypal_status);
            }
        })->get();

        foreach ($participants as &$p) {
            // echo 'projeciot' . $request['project_id'];
            // echo 'USERID' . $p->user_id;
            // echo $p->userid;
            $pp = ProjectParticipant::where("projects_projectid", $request['project_id'])->where("participants_userid", $p->user_id)->first();

            if ($pp) {
                // var_dump($pp);
                $p->projectParticipantInvited = $pp->invited;
                $p->projectParticipantPaymentConfirmed = $pp->payment_confirmed;
                // exit;
            } else {
                // $p->projectParticipantInvited =null;
                // $p->projectParticipantPaymentConfirmed = null;
            }
        }


        // var_dump($participants->toArray());
        // exit;



        $include_subroles = [];
        if ($include_seeds) {
            $include_subroles[] = "seed";
        }
        if ($include_peers) {
            $include_subroles[] = "friend";
        }
        $participants = $this->filter_by_role($participants, $include_subroles);

        return new ParticipantsResource($participants);
    }

    private function filter_by_role($pArray, $roleArray = [])
    {
        $return = [];
        foreach ($pArray as $user) {
            if (isset($user->user->subrole)) {
                if (in_array($user->user->subrole, $roleArray)) {
                    $return[] = $user;
                }
            }
        }
        return $return;
    }

    /**
     * Store a newly created Participant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        //handled elsewhere
    }
    /**
     * Display the specified Participant.
     *
     * @param  \App\Participant  $participant
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $user = $this->retrieve_profile($id);
        return $this->sendResponse($user, 'User retrieved');
    }

    /**
     * Allow a user to request their own profile information
     */
    public function show_profile()
    {
        $user = Auth::user();
        $profile = $this->retrieve_profile($user->id);
        $profile['step'] = $this->getUserStep();
        $template = EmailTemplate::where('subject', "MySurveys Friend Invitation")->first();
        $emailCtrl = new EmailTemplateController();
        if ($template) {
            $body_subject = $emailCtrl->transformEmailTemplateBodySubject($template, null, $user);
            $body = $body_subject['body'];
            $profile['template'] = $body_subject;
        }
        return $this->sendResponse($profile, 'Profile retrieved');
    }

    // Helper
    public function getUserStep($user = null)
    {
        if (!$user) {
            $user = Auth::user();
        }
        $u = User::find($user->id);
        $friends = count(Participant::where("seed_id", $user->id)->get());
        if ($u->subrole === 'seed') {
            if ($u->participant->paypal_id_status !== 'Ok') {
                return 'paypal';
            }
            $friends = Participant::where("seed_id", $u->id)->get();
            if (!count($friends)) {
                return 'friends';
            }
        }
        if ($u->subrole === 'friend') {
            if ($u->changed_pw !== 1) {
                return 'profile';
            }
            if ($u->participant->survey_complete !== True) {
                return 'questionnaire';
            }
            if ($u->participant->paypal_id_status !== 'Ok') {
                return 'paypal';
            }
        }
        return "";
    }

    /**
     * Retrieve profile from DB
     * @param type $id
     */
    private function retrieve_profile($id)
    {

        $u = User::with('participant')->where('id', $id)->first();
        $u->participant->load('friends');
        $u->participant->load('seed');
        if ($u->participant->seed) {
            $u->participant->seed->load('user');
        }
        $u->participant->friends->load('user');

        $ua = array_merge($u->toArray(), $u->participant ? $u->participant->toArray() : []);
        unset($ua['participant']);
        $ua['subrole'] = $u->subrole;
        $ua['role'] = $u->getRoleNames()[0];
        return $ua;
    }

    /**
     * Update the specified Participant in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Participant  $participant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $p = Participant::find($id);
        $input = $request->all();
        $validator = Validator::make($input, Participant::validator());
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $p->update($validator->valid());
        $this->logger('info', Auth::user()->email . ' updated ' . $p->email, $validator->valid());
        return $this->sendResponse($p, 'Profile updated successfully.');
    }

    /**
     * Remove the specified Participant from storage.
     *
     * @param  \App\Participant $participant
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {


        $friends_ids = Participant::where("seed_id", $id)->pluck('user_id');

        $participants_ids = ProjectParticipant::where("participants_userid", $id)->pluck('participants_userid');


        if (count($friends_ids)) {
            return $this->sendResponse($friends_ids, "This user has active friends, please delete those first " . "[$friends_ids]", 304);
        }

        if (Auth::user()->role !== 'administrator') {
            return $this->sendError("Only administrators can delete");
        }

        if ($id == 1) {
            return $this->sendError("Don't delete the administrator");
        }
        ProjectParticipant::where("participants_userid", $id)->delete();

        Participant::where("user_id", $id)->delete();
        $d = User::find($id)->delete();

        if ($d) {
            return $this->sendResponse("Deleted user", 200);
        }
        return $this->sendError("Problem deleting user", 401);
    }
}
