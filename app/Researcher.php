<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Researcher extends Model
{

    protected $primaryKey = 'user_id'; // or null
    //
    protected $fillable = ["user_id", "nickname"];

    /**
     * The users projects
     */
    public function projects()
    {
        return $this->hasMany('App\Project', 'project_users', 'project_id', 'researcher_id');
    }

    //researchers user 
    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
