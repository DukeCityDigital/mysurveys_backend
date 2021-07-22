<?php

namespace App\Http\Controllers;

use App\ProjectParticipant;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Validator;
use App\Project;
use App\Http\Resources\ProjectParticipants;
use App\Participant;
use App\User;
use Illuminate\Support\Facades\Hash;

class ProjectParticipantController extends BaseController
{



    /**
     * Display a listing of the Project Participants.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        return $this->sendResponse(ProjectParticipant::all(ProjectParticipant::paginate()), 'Project Participants retrieved');
    }

    /**
     * Project Participants Selection
     *
     * @return \Illuminate\Http\Response
     */
    public function project_participants(Request $request)
    {

        if ($this->validateFilter($request)) {
            $r = $request['filter'];

            $multi = explode("|", $r);

            $conditions = [];
            foreach ($multi as $condition) {
                $operator = !(stripos($condition, "!") !== false);
                $field_actual = trim($condition, "!");
                $conditions[] = ["field" => $field_actual, "operator" => $operator];
            }
            $init = ProjectParticipant::where("projects_projectid", $request['project_id'])->orderBy($request['sort'], $request['order']);

            foreach ($conditions as $c) {
                if ($c['operator']) {
                    $init->whereNotNull($c['field']);
                } else {
                    $init->whereNull($c['field']);
                }
            }
            $selected_ids = $init->pluck('participants_userid');

            $resource = new ProjectParticipants($init->paginate());
            //hack to copy resosurce w/o constraints
            $with_ids = $this->add_selected_ids($resource, $selected_ids);
            return $with_ids;
        }
        $r = ProjectParticipant::where("projects_projectid", $request['project_id'])->orderBy($request['sort'], $request['order'])->paginate();
        $with_ids = $this->add_selected_ids($r, $r->pluck('participants_userid'));

        return $with_ids;
    }

    /**
     * Copy resource and add the IDS to send all invitations, not just paginated result
     * @param type $resource
     * @param type $selected_ids
     * @return type
     */
    private function add_selected_ids($resource, $selected_ids)
    {
        $j = json_encode($resource);
        $jr = json_decode($j);
        $jr->selected_ids = $selected_ids;
        return json_encode($jr);
    }

    /**
     * Get project participants participants
     *
     * @return \Illuminate\Http\Response
     */
    public function get_selection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'project_id' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Missing project ID');
        }

        if (isset($request->all()['all'])) {
            // $Project = Project::find($request['project_id']);
            $pp = ProjectParticipant::with(['participant', 'user'])->where("projects_projectid", $request['project_id'])->get();

            $ra = [];
            $csv_new = ['participants_userid'];


            $data = [];
            $ra = [];
            foreach ($pp as $key => &$ppayee) {

                // echo 'seedid';
                // echo $ppayee->participant->seed_id;

                $seed = Participant::where("user_id", $ppayee->participant->seed_id)->first();

                $friends = Participant::where("seed_id", $ppayee->participants_userid)->get();
              
                if ($seed) {
                    $seed_nickname = $seed->nickname;
                } else {
                    $seed_nickname = null;
                }
                $fields = array(
                    'created' => $ppayee->user->created_at,                    
                    'email' => $ppayee->user->email,
                    'project_id' => $ppayee->projects_projectid,
                    'invited' => $ppayee->invited,
                    'started' => $ppayee->started,
                    'finished' => $ppayee->amount_to_pay,
                    'validated' => $ppayee->validated,
                    'paymentorders_payorderid' => $ppayee->paymentorders_payorderid,
                    'payment_confirmed' => $ppayee->payment_confirmed,
                    'safeid' => $ppayee->safeid,
                    'is_seed' => $ppayee->participant->is_seed,
                    'paypal_id' => $ppayee->participant->paypal_me,
                    'paypal_id_status' => $ppayee->participant->paypal_id_status,
                    'qualified' => $ppayee->participant->qualified,

                    'qualification_vac_receive' => $ppayee->participant->qualification_vac_receive,
                    'qualification_vac_benefit' => $ppayee->participant->qualification_vac_benefit,
                    'qualification_vac_effective' => $ppayee->participant->qualification_vac_effective,
                    'qualification_vac_harmful' => $ppayee->participant->qualification_vac_harmful,
                    'qualification_vac_pharma' => $ppayee->participant->qualification_vac_pharma,
                    'qualification_share_answers' => $ppayee->participant->share,

                    'qualification_share_info' => $ppayee->participant->share_data,


                    'qualification_us' => $ppayee->participant->qualification_us,

                    'amount_to_pay' => $ppayee->amount_to_pay,
                    'payment_confirmed' => $ppayee->payment_confirmed,
                    'subrole' => $ppayee->user->subrole,
                    'participants_userid' => $ppayee->participants_userid,
                    'seed_id' => $ppayee->participant->seed_id,
                    'nickname' => $ppayee->participant->nickname,
                    'seed_nickname' => $seed_nickname,
                    'source' => $ppayee->participant->source,
                    // 'friend1' => $friend1,
                    // 'friend2' =>$friend2,
                    // 'friend3' =>$friend3,
                    // 'friend4' =>$friend4,
                );
                $count = 0;
                foreach ($friends as $f) {
                    $key = 'friend' . $count;

                    $fields[$key] = $f->nickname;



                    $count++;
                }
            
                $data[] = $fields;
                $ppayee['paypal_id'] = $ppayee->user->email;
                $p = [];
                $p['paypal_id'] = $ppayee->user->email;
                $p['payment_amount'] = $ppayee->amount_to_pay;
                $p['currency'] = 'USD';
                $p['customer_id'] = $ppayee->user->email;
                $p['note'] = '';
                $p['wallet'] = 'PAYPAL';
                $ra[] = $p;
            }
            return $this->sendResponse(["projectparticipants" => $pp, "csv" => $ra, "v2csv" => $data], 'PayPal Formatted Project Participants');
        }

        return new ProjectParticipants(ProjectParticipant::where("projects_projectid", $request['project_id'])->paginate(0));
    }

    /**
     * STore project participants
     *
     * @return \Illuminate\Http\Response
     */
    public function create_selection(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'project_id' => 'required',
        ]);
        if (!isset($request['users'])) {
            $users = [];
        } else {
            $request['users'] = array_filter($request['users']);
        }

        if ($validator->fails()) {
            return $this->sendError('Please complete the form');
        }

        $new = 0;

        if (!isset($request['update'])) {
            ProjectParticipant::where("projects_projectid", $request['project_id'])->delete();
        }

        foreach ($request['users'] as $user) {

            $data = [
                "projects_projectid" => $request['project_id'],
                "participants_userid" => $user,
                "safeid" => $this->getName($user, $request['project_id'])
            ];

            $p = new ProjectParticipant();

            $exists = ProjectParticipant::where("projects_projectid", $request['project_id'])
                ->where("participants_userid", $data['participants_userid'])->first();

            if (!$exists) {
                $p->fill($data);
                $p->save();
                $new++;
            }
        }
        return $this->sendResponse('Added ' . $new . ' Participants; ', 201);
    }

    private function getName($user, $project_id)
    {
        $user_actual = User::find($user);
        $string = $user_actual->email . $project_id;
        $hashed = md5($string);
        return substr($hashed, -12);       
    }

    private function validateFilter($request)
    {
        if (isset($request['filter']) && $request['filter'] !== '' && $request['filter'] !== 'undefined') {
            return $request['filter'];
        }
    }
}
