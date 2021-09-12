<?php

namespace App;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\VerifyApiEmail;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Auth\Passwords\CanResetPassword;
use App\Notifications\ResetPasswordEmail;
use App\Notifications\EmailChangeNotification;
use App\Notifications\PasswordResetSuccess;
use App\Notifications\ProjectInvitation;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\CustomMessage;
use App\Notifications\EmailTemplateMessage;
use App\Notifications\FriendVerifyApiEmail;



class User extends Authenticatable implements JWTSubject, MustVerifyEmail, ShouldQueue
{

    use CanResetPassword;
    use Notifiable;
    use HasRoles;

    protected $guard_name = 'api';
    protected $appends = ['role', 'subrole'];

    /**
     * The attributes that are mass assignable.
     * f
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'email', 'password', 'verification_code', 'id', 'banned', 'banned_reason', 'warnings'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Determine if the user has verified their email address.
     *
     * @return bool
     */
    public function hasVerifiedEmail()
    {
        return $this->email_verified_at !== null;
    }

    //researcher relation w default
    public function researcher()
    {
        return $this->hasOne('App\Researcher');
    }

    public function flatten()
    {
        $f = $this->friends()->get();
        return $f;
    }

    public function friends()
    {
        $f = $this->hasMany('App\Participant', 'seed_id', 'id');
        return $f;
    }


    public function getRoleAttribute()
    {
        return $this->getRoleNames()[0];
    }

    public function getSubroleAttribute()
    {
        if ($this->getRoleNames()[0] == 'participant') {
            $p = Participant::where("user_id", $this->id)->first();
            if ($p && $p->seed_id) {
                return 'friend';
            }
            return 'seed';
            // $user = User::with('participant')->where('id', $this->id)->first();
            // var_dump($user->toArray());
            // return $user->participant->is_seed ? 'seed' : 'friend';
        }
    }

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->hasVerifiedEmail() ? $this->getRoleNames()[0] : null,
        ];
    }



    /**
     * Check for duplicate user, return email if new
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public static function checkIfNew($email)
    {
        $user = User::where('email', '=', $email)->first();

        if ($user === null) {
            return $email;
        } else {
            return false;
        }
    }

    /**
     * Send email template message
     * @param
     */
    public function sendEmailTemplateMessage($data = [])
    {
        $this->notify(new EmailTemplateMessage($data));
    }

    /**
     * Project invitation email
     * @param array Project
     */
    public function sendInvitationNotification($data = [])
    {

        
        $this->notify(new ProjectInvitation($data));
    }

    /**
     * API Verification email
     */
    public function sendApiEmailVerificationNotification($data = [])
    {
        $this->notify(new VerifyApiEmail($data));
    }


    /**
     * API Verification email for friends
     */
    public function friendSendApiEmailVerificationNotification($data = [])
    {
        $this->notify(new FriendVerifyApiEmail($data));
    }

    /**
     * Send reset PW notif
     */
    public function sendPasswordResetSuccessNotification($data = [])
    {
        $this->notify(new PasswordResetSuccess($data));
    }

    /**
     * reset PW
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordEmail($token));
    }

    /**
     * change email
     */
    public function sendEmailChangeNotification($token)
    {
        $this->notify(new EmailChangeNotification($token, $this->email));
    }

    /**
     * Change email token
     */
    public function getChangeEmailToken()
    {
        $token = EmailChanges::where("email", $this->email)->first();
        return isset($token) ? $token : null;
    }

    // user/s participant 
    public function participant()
    {
        return $this->hasOne('App\Participant', 'user_id');
    }
}
