<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\ProjectParticipant;

class Project extends Model
{

    protected $fillable = [
        "project_title", "description",
        "id", "defaultend", "defaultstart", "responsible_person", "link",
        // "link_method",
        "payout_type", "max_payout", "exp_payout",
        "desired_sample_size",
        "state", "start_state", "created_at",
        "desired_num_invitations",
        "creator_userid", "quota"
    ];


    public static function validator()
    {
        return [
            'project_title' => 'required|string',
            // 'defaultstart' => 'required|date',
            // 'defaultend' => 'required|date|after_or_equal:defaultstart',
        ];
    }

    /**
     * The participants that belong to the project
     */
    public function projectParticipants()
    {
        return $this->hasMany('App\ProjectParticipant', 'projects_projectid', 'id');
    }

    /**
     * The users that belong to the project
     */
    public function researchers()
    {
        return $this->belongsToMany('App\Researcher', 'project_users', 'project_id', 'id');
    }

    /**
     * Test whether the default end date has elapsed
     * @return bool
     * TODO tests
     */
    public function getDefaultEndDateElapsedAttribute()
    {

        $start_dt = new \DateTime($this->defaultend);
        $now = new \DateTime();
        if ($now > $start_dt) {
            return 'The default end date has elapsed';
        }
    }

    /**
     * Check exp. payout exceeds quota
     */
    public function expected_payout_exceeds_quota($participants)
    {
        // ...
    }

    public function get_number_of_participants()
    {
        return ProjectParticipant::where("projects_projectid", $this->id)->count();
    }

    /**
     * Check the project state
     * @param type $query
     */
    public function scopeState($query, $state)
    {
        return $query->where("state", $state);
    }

    /**
     * Check the project start state
     * @param type $query
     */
    public function scopeStartState($query, $state)
    {
        return $query->where("start_state", $state);
    }

    /**
     * Cost of invitations currently being proposed
     */
    public function proposed_invitations_cost($participants)
    {
        return $this->max_payout * count($participants);
    }

    /**
     * Test if user is trying to invite more than the amount desired in project
     * @return bool
     */
    public function proposed_invitations_exceed_desired_number($participants)
    {
        $number_proposed = count($participants);
        $number_invited = ProjectParticipant::where("projects_projectid", $this->id)
            ->whereNotNull("invited")->get()->count();

        if ($number_proposed + $number_invited > $this->desired_num_invitations) {
            return true;
        }
    }

    /**
     * Get amount of quota already used
     * @return float
     */
    public function getUsedQuotaAttribute()
    {
        $participants = ProjectParticipant::where("projects_projectid", $this->id);
        return $participants->sum('amount_to_pay');
    }

    public function getNumberStartedAttribute()
    {
    }

    /**
     * Get number of participants finished with project
     * @return int
     */
    public function getNumberFinishedAttribute()
    {
        $participants = ProjectParticipant::where("projects_projectid", $this->id)->whereNotNull('finished')->get();
        return count($participants);
    }
}
