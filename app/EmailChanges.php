<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailChanges extends Model {

    protected $fillable = [
        'email', 'token', 'new_email'
    ];

}
