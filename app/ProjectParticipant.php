<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProjectParticipant extends Model {

    protected $primaryKey = 'participants_userid';
    protected $table = "project_participant";
    protected $fillable = ["participants_userid", "projects_projectid", "safeid"];

    public function user() {
        return $this->hasOne('App\User', 'id', 'participants_userid');
    }

    public function participant() {
        return $this->hasOne('App\Participant', 'user_id', 'participants_userid');
    }

    public function project() {
        return $this->hasOne('App\Project', 'id', 'projects_projectid');
    }

    public function invitations() {
        return $this->hasOne('App\Project', 'id', 'projects_projectid')->where("state", "Started")->where("start_state", "Open");
    }

}
