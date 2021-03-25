<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{

    protected $fillable = ["paypal_api_endpoint", "test_mode", "updated_at", 'researchermessage', 'participantmessage'];

    public static function validator()
    {
        return [
            'test_mode' => 'boolean',
            'researchermessage' => 'string',
            'participantmessage' => 'string',
            'paypal_api_endpoint' => 'nullable|string'
        ];
    }
}
