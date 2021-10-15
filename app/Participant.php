<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Project;
use App\ProjectParticipant;

class Participant extends Model
{

    /**
     * The attributes that are mass assignable.
     * f
     * @var array
     */
    protected $fillable = [
        'user_id',
        'ip',
        'share_data',
        'share',
        'qualified',
        'nickname',
        'is_seed', 'first_name', 'family_name', 'birthyear',
        "qualification_us", "qualification_parents",
        "qualification_friends", "qualification_gm",
        'street', 'zip', 'city', 'seed_id', 'paypal_id',
        'paypal_id_status', 'paypal_me', "qualification_vac",
        "source",
        "qualification_vac_receive",
        "qualification_vac_benefit",
        "qualification_vac_effective",
        "qualification_vac_harmful",
        "qualification_vac_pharma",
    ];
    protected $appends = array('projects', 'verified_friends_count', 'eligible_seed', 'email', 'survey_complete');
    protected $primaryKey = 'user_id';

    public static function validator()
    {
        return [
            'first_name' => 'nullable|string',
            'paypal_id_status' => 'string'
        ];
    }

    public function getProjectsAttribute()
    {
        $p = ProjectParticipant::where("participants_userid", $this->user_id)->get();
        foreach ($p as &$projectp) {
            $projectp->project_title = Project::where("id", $projectp->projects_projectid)->first()->project_title;
        }
        return $p;
    }




    /**
     * Check for duplicate user, return email if new
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function checkIfNew($id)
    {
        $p = Participant::where('user_id', '=', $id)->first();

        if ($p === null) {
            return $p;
        } else {
            return false;
        }
    }

    /**
     * Designate the user as a seed
     */
    public static function makeSeed($id, $form)
    {

        $source = isset($form['source']) ? $form['source'] : null;

        $p = Participant::where('user_id', '=', $id)->first();
        $p->is_seed = 1;
        // $p->qualification_parents = $form['parents'] == true;
        // $p->qualification_friends = $form['friends'] == true;
        // $p->qualification_gm = $form['gm'];
        $p->qualification_vac_receive = $form['vac_receive'];
        $p->qualification_vac_benefit = $form['vac_benefit'];
        $p->qualification_vac_effective = $form['vac_effective'];
        $p->qualification_vac_harmful = $form['vac_harmful'];
        $p->qualification_vac_pharma = $form['vac_pharma'];
        $p->qualification_us = $form['us'] == true;
        $p->share_data = isset($form['share_info']);
        $p->share = isset($form['share']);

        $p->qualified = $form['qualified'] == true;

        $p->source = $source;

        $p->save();
        return $p;
    }

    /**
     * Participant Friends
     */
    public static function getFriends($id)
    {
        $f = Participant::where("seed_id", $id)->get();
        return $f;
    }

    /**
     * Participant's friends who are validated
     */
    public function verified_friends()
    {
        //        TODO check verified
        return $this->friends();
        $f = $this->hasMany('App\Participant', 'seed_id');


        $has_verified_friend = 0;
        foreach ($f as $friend) {
            if ($friend->paypal_id_status == 'Ok') {
                $has_verified_friend++;
            }
        }
        return $has_verified_friend;
    }

    /**
     * projects or PP's that the Participant is associated with
     * @return type
     */
    public function participation()
    {
        return $this->hasMany('App\ProjectParticipant', 'participants_userid', 'user_id');
    }

    public function friends()
    {
        return $this->hasMany('App\Participant', 'seed_id', 'user_id');
    }

    public function eligible_seed()
    {
        $friends = $this->has_verified_friends();
        $paypal_ok = $this->paypal_id_status == 'Ok';
        if ($friends && $paypal_ok) {
            return true;
        }
        return false;
    }

    public function getEligibleSeedAttribute()
    {
        return true;
    }

    public function getSurveyCompleteAttribute()
    {
        return !is_null($this->qualification_us);
    }

    public function getEmailAttribute()
    {
        $u =  User::find($this->user_id);
        if ($u) return $u->email;
        // return User::find($this->user_id)->email;
    }

    public function getVerifiedFriendsCountAttribute()
    {
        $f = $this->friends()->get();
        return count($f->where("paypal_id_status", "Ok"));
    }

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function seed()
    {
        return $this->hasOne('App\Participant', 'user_id', 'seed_id');
    }

    public function is_eligible_seed()
    {
        return $this->friends() ? True : False;
    }
}
