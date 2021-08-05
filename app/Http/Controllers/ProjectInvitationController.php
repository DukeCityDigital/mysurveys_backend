<?php

namespace App\Http\Controllers;

use App\EmailTemplate;
use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Participant;
use App\ProjectParticipant;
use Validator;
use App\User;
use App\Project;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Sendlist;
use Egulias\EmailValidator\Warning\EmailTooLong;

use function Ramsey\Uuid\v1;

// $pp = ProjectParticipant::with(['user' => function ($query) use ($project_id) {
//     $query->where('projects_projectid', '=', $project_id);
// },])->get();
//
// $tournaments = Tournament::with(['championships' => function ($query) {
//     $query->where('something', 'like', '%this%');
// },
// 'championships.settings' => function ($query) {
//     $query->where('something', 'like', '%this%');
// },
// ...])->get();
class ProjectInvitationController extends BaseController
{

    /**
     * Send custom message to selected participants
     * @param Request $request
     * @return type
     */
    public function send_custom_message(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'template_id' => 'required',
        ]);
        if ($validator->fails()) {
            return $this->sendError(implode(",", $validator->messages()->all()), implode(",", $validator->messages()->all()));
        }
        $ids = $request['ids'];
        $data = $request->all();
        $data['project'] = Project::find($data['project_id']);

        $template = EmailTemplate::find($request['template_id']);
        $data['body'] = $template['body'];
        $data['subject'] = $template['subject'];

        if ($request['test']) {
            $user = Auth()->user();
            $user->sendEmailTemplateMessage($data);
            return $this->sendResponse('Test email sent to ' . $user->email, $user->email);
        }

        $users_actual = User::with('participant')->whereIn("id", $ids)->get();
        $d = new \DateTime();
        $now = $d->format("Y-m-d H:i:s");

        foreach ($users_actual as $message_target) {
            //            TODO factor into env
            if (strpos(strtolower($template->subject), "invit") !== false) {
                // var_dump($template, $message_target->id);
                $participant = ProjectParticipant::where("participants_userid", $message_target->id)->first();
                $participant->invited = $now;
                $participant->save();
            }
            

            sleep(1);
            $message_target->sendEmailTemplateMessage($data);
            $this->logger('info', "Custom message sent to participant", ["user" => $message_target]);
        }
        return $this->sendResponse($ids, 'ids');
    }

    /**
     * Send project invitations
     */
    public function send_project_invitations(Request $request)
    {

        //default as DEV for no actual sending


        $validator = Validator::make($request->all(), [
            'ids' => 'required',
            'project_id' => 'required',
            'TEST_MODE' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Missing required ids/dev');
        }
        $project_id = $request['project_id'];
        $ids = $request['ids'];

        if ($validator->valid()['TEST_MODE'] == 'PRODUCTION') {
            $DEV = 'PRODUCTION';
        }

        $user_ids = ProjectParticipant::where("projects_projectid", $project_id)
            ->whereIn("participants_userid", $ids)->pluck('participants_userid');

        $users_actual = User::with('participant')->whereIn("id", $user_ids)->get();
        $invitation_errors = $this->check_if_invitation_is_ok($project_id, $user_ids);



        if (!empty($invitation_errors)) {
            return $this->sendResponse(implode(",", $invitation_errors), $invitation_errors, 400);
        }

        $pp_actual = ProjectParticipant::whereIn("participants_userid", $ids)
            ->where("projects_projectid", $project_id)
            ->get();

        $d = new \DateTime();
        $now = $d->format("Y-m-d H:i:s");

        $project_actual = Project::find($project_id);
        $this->logger('info', "Initiating invitations group", ["ids" => $ids, "project_id" => $project_id]);
        $count = 0;

        /**
         * Initiate sending sequence
         */
        $project_actual->state = "Started";
        $project_actual->save();
        $data = $project_actual->toArray();
        $data = array_merge($data, ["remind" => isset($request->all()['reminder'])]);

        foreach ($pp_actual as $invitee_participant) {
            sleep(1);
            DB::transaction(function () use ($data, $invitee_participant, $now,  $project_id, &$count) {
                $data['participant'] = $invitee_participant;
                $invitee_participant->invited = $now;
                $invitee_participant->save();

                $user_actual = User::find($invitee_participant->participants_userid);
                $user_actual->sendInvitationNotification($data);
                $count = $count + 1;
                $this->logger('info', "Invitation sent to participant " . $user_actual->email, ["user" => $user_actual, "project_id" => $project_id]);

                $slData = [
                    "projects_projectid" => $project_id,
                    "replyto" => 'support',
                    "mailto" => $user_actual->email,
                    "subject" => "Invitation to study",
                    "body" => "InvitationContent",
                    "lastattempt" => $now
                ];
                $sendlist = new Sendlist();
                $sendlist->create($slData);
            });
        }

        return $this->sendResponse($count . " Invitations sent", 200);
    }


    /**
     * Make sure the selected participants are valid etc
     * test banned, email verification, paypal OK
     * @return Bool
     * TODO - more elegant way?
     */
    public function checkIfParticipantsAreOk($project_id, $ids)
    {

        $participant_ids = $ids;
        $errors = [];

        $omnitest = ProjectParticipant::with(['user', 'participant'])->whereIn("participants_userid", $participant_ids)
            ->where("projects_projectid", $project_id)
            ->get();

        foreach ($omnitest as $usr) {
            if ($usr->participant->paypal_id_status !== 'Ok') {
                $errors[0] = 'Error - 1 or more users has not verified PayPal';
            }
            if ($usr->user->email_verified_at == NULL) {
                $errors[1] = 'Error - 1 or more users has not validated email';
            }

            if ($usr->user->banned) {
                $errors[2] = 'Error - 1 or more users is banned';
            }
        }
        return $errors;
    }

    /**
     * Run multiple checks on invitation
     */
    private function check_if_invitation_is_ok($project_id, $ids)
    {

        $errors = $this->checkIfParticipantsAreOk($project_id, $ids);

        $user_ids = ProjectParticipant::where("projects_projectid", $project_id)
            ->whereIn("participants_userid", $ids)->pluck('participants_userid');

        $project = Project::find($project_id);

        if ($project->default_end_date_elapsed) {
            $errors[] = $project->default_end_date_elapsed;
        }

        // disabling per feedback 
        // if (!$project->exp_payout) {
        //     $errors[] = 'The project has no expected payout';
        // }

        // if (!$project->max_payout || $project->max_payout == 0) {
        //     $errors[] =  'No max payout is set for the project';
        // }

        // $total_max_payout = $project->get_number_of_participants() * $project->max_payout;
        // if ($total_max_payout > $project->quota) {
        //     $errors[] =  'The total max payout (participants X max_payout) for the project is greater than the project quota';
        // }

        if ($project->state !== 'Started') {
            $errors[] = 'Project state is ' . $project->state;
        }

        if ($project->start_state !== 'Open') {
            $errors[] = 'Project start state is ' . $project->start_state;
        }


        $participants_actual = ProjectParticipant::whereIn("participants_userid", $user_ids)
            ->where("projects_projectid", $project_id)
            ->get();

        $proposed_invitations_cost = $project->proposed_invitations_cost($participants_actual);

        $used_quota = $project->used_quota;

        $proposed_total = $used_quota + $proposed_invitations_cost;

        if ($proposed_total > $project->quota) {
            $errors[] = 'The proposed total of ' . $proposed_total . " exceeds the project quota of " . $project->quota;
        }

        $number_finished = $project->number_finished;
        if ($number_finished > $project->desired_sample_size) {
            $errors[] = "Sample size reached!";
        }

        // $invitation_threshold = $project->proposed_invitations_exceed_desired_number($participants_actual);
        // if ($invitation_threshold) {
        //     $errors[] = "The proposed number of invitations plus the number already invited exceeds the desired number for the project";
        // }
        return $errors;
    }

    /**
     * Change start state of project to open if no errors
     * @param type $project_id
     * @param type $errors
     */
    private function updateStartState($project_id, $errors)
    {
        $start_state = "Closed";
        if (empty($errors)) {
            $start_state = "Open";
        }
        $p = Project::find($project_id);
        $p->start_state = $start_state;
        $p->save();
    }

    /**
     * Test whether the project is over - end date elapsed
     */
    private function check_project_time_elapsed($project_id)
    {
        $project = Project::find($project_id);
        $start_dt = new \DateTime($project->defaultend);
        $now = new \DateTime();
        if ($now > $start_dt) {
            return false;
        }
        return true;
    }

    //possibly obsolete
    private function buildApiProjectInviteEmail()
    {
    }

    private function selectInvitedParticipants()
    {
    }

    private function updateInvitedParticipants()
    {
    }

    private function updateProjectstartState()
    {
        // Check if date has passed 'defaultend' and that the quota is ok and
        // that the desired sample size has not been reached yet
        // then update project if necessary
        // call the project started if OK
    }
}
