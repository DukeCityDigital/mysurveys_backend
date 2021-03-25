<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Sendlist extends Model
{
    //
    protected $table = "sendlist";
    protected $fillable = ["projects_projectid", "mailto", "subject", "body", "replyto", "lastattempt"];
}
