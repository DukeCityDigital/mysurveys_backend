<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\PasswordReset;
use App\EmailChanges;
use Illuminate\Support\Facades\Auth;
use App\Notifications\PasswordResetSuccess;
use App\Participant;
use App\Helpers\Utilities;

class RegisterController extends BaseController
{

    //


    public function __construct()
    {
        //        TODO - check updated docs 
        //        $this->middleware('auth:api', ['except' => ['login']]);
    }


    /**
     * Register the user
     * @param Request $request
     * @return type
     */
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'qualificationForm' => 'array'
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 409);
        }
        $user_info = $validator->valid();

        if ($newuser = $this->create_user($user_info, 'participant')) {
            $this->create_participant($newuser->id);
            if (isset($user_info['qualificationForm'])) {
                Participant::makeSeed($newuser->id, $user_info['qualificationForm']);
            }
            $newuser->sendApiEmailVerificationNotification($user_info);
            return response()->json(['user' => $newuser], 201);
        }
    }

    /**
     * Send the user a reminder to accept the friend invitation
     * @param Request $request
     * @return type
     */
    public function invite_reminder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 400);
        }
        $user = User::where("email", $validator->valid()['email'])->first();

        if (!$user) {
            return response()->json('User not found', 404);
        }
        if ($user->email_verified_at) {
            return response()->json('User has already verified email', 400);
        }
        $password = Utilities::generateUUID(12);
        $this->updatePassword($user->id, $password);
        $data = $user;
        $data['invite'] = true;
        $data['password'] = $password;
        $user->sendApiEmailVerificationNotification($data);
    }

    /**
     * Invite participant / friend
     * @param Request with key 'invite' treated as friend invite
     * @return type
     */
    public function invite_participant(Request $request)
    {

        if (!empty($request->all()['remind'])) {
            return $this->invite_reminder($request);
        }

        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 400);
        }

        $data = $validator->valid();
        if (isset($request['custom_message'])) {
            $data['custom_message'] = $request->all()['custom_message'];
        }
        $data['password'] = Utilities::generateUUID(12);
        $newuser = $this->create_user($data, 'participant');
        if ($newuser && Auth::user()) {
            $seed_id = Auth::user()->id;
            $p = $this->create_participant($newuser->id, $seed_id);
            if (isset($request['invite'])) {
                $data['invite'] = true;
                $newuser->friendSendApiEmailVerificationNotification($data);

                return response()->json(['user' => $newuser], 201);
            }
            if (!$p) {
                return $this->sendError('An error occurred');
            }
            $newuser->sendApiEmailVerificationNotification($data);
            return response()->json(['user' => $newuser], 201);
        } else {
            return $this->sendError('An error occurred');
        }
        //        TODO clarify
        return response()->json(['user exists'], 500);
    }


    /**
     * Invitee submits qualification form
     */
    public function user_submit_qualification_form(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'qualificationForm' => 'required|array'
        ]);
        $user = Auth::user();
        if ($validator->fails()) {
            $errors = [];
            foreach ($validator->messages()->toArray() as $key => $message) {
                $errors[] = $message[0];
            }
            return response()->json($errors, 400);
        }
        $formData = $request['qualificationForm'];
        foreach ($formData as $key => &$value) {
            $value = $value == 'true' ? 1 : $value;
        }
        $share_data = isset($formData['share_data']) ? $formData['share_data'] : null;
        $fda = array(
            "qualification_vac_receive" => $formData['vac_receive'],
            "qualification_vac_benefit" => $formData['vac_benefit'],
            "qualification_vac_effective" => $formData['vac_effective'],
            "qualification_vac_harmful" => $formData['vac_harmful'],
            "qualification_vac_pharma" => $formData['vac_pharma'],
            "qualification_vac_receive" => $formData['vac_receive'],

            // "qualification_parents" => $formData['parents'],
            // "qualification_gm" => $formData['gm'],
            // "qualification_vac" => $formData['vac'],
            // "qualification_us" => $formData['us'],
            // "qualification_friends" => $formData['friends'],
            "share_data" => $share_data,
            "qualified" => $formData['qualified'],


        );
        $participant = Participant::where("user_id", $user->id)->first();

        $participant->fill($fda);
        $participant->save();

        return $this->sendResponse('submitted', 200);
    }


    /**
     * Resend the verification Email
     * @param Request $request
     * @return type
     */
    public function resend_verification_email(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 400);
        }
        $user = User::where('email', $validator->valid()['email'])->first();
        // echo 'k';
        // echo $user;
        // exit;
        $data = array(
            'resend' => true
        );

        $user->sendApiEmailVerificationNotification($data);
        return response()->json(['message' => 'Request received, please check your email'], 200);
    }

    /**
     * Initiate password reset
     * @param Request $request
     * @return type
     */
    public function reset_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 400);
        }

        $user = User::where("email", $validator->valid()['email'])->first();
        if (!$user) {
            return $this->sendResponse('User not found', 'User not found', 404);
        }

        $message = 'A password reset request has been received.  Please check your email';
        $token = (string) Str::uuid();
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $token
            ]
        );
        $this->logger("info", 'User requested password reset' . $request->all()['email'], $validator->valid());

        if ($user && $passwordReset) {
            $user->sendPasswordResetNotification($token);
        }
        return response()->json(["message" => $message], 200);
    }

    /**
     * Reset the users password ACTUAL
     * @param Request $request
     */
    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
            'token' => 'required'
        ]);
        if ($validator->fails()) {
            return $this->sendError('Please complete the form');
        }

        $password = $request->password;
        $tokenData = DB::table('password_resets')
            ->where('token', $request->token)->first();
        if (!$tokenData) {
            return $this->sendError('Token invalid');
        }

        $user = User::where('email', $tokenData->email)->first();
        if (!$user) {
            return $this->sendError('Email not found');
        }

        $user->password = \Hash::make($password);
        $user->update(); //or $user->save();
        //login the user immediately they change password successfully
        Auth::login($user);
        //Send Email Reset Success Email
        if ($user->notify(new PasswordResetSuccess($user)) !== false) {
            //Delete the token
            $d = DB::table('password_resets')->where('email', $user->email)
                ->delete();
            $this->logger("info", 'User succeeded password reset ' . $user->email, [$user->email]);

            if ($token = auth()->login($user)) {
                $aCtrl = new AuthController();
                return $aCtrl->respondWithToken($token, $user);
            }
        } else {
            return $this->sendError('A Network Error occurred');
        }
    }

    /**
     * Create participant for given user
     * @param type $id
     * @param type $seed_id
     * @return boolean
     */
    public function create_participant($id, $seed_id = null)
    {
        $aData = [
            "user_id" => $id,
            "seed_id" => $seed_id,
        ];
        $new = Participant::checkIfNew($id);
        if ($new === false) {
            return response()->json(['error' => 'participant exists'], 409);
        }
        $data = Participant::create($aData);
        if ($data) {
            return $data;
        }
        return false;
    }

    /**
     * Update password helper
     * @param type $user_id
     * @param type $password
     */
    private function updatePassword($user_id, $password)
    {

        $u = User::find($user_id);
        $u->password = bcrypt($password);
        $u->save();
    }

    /**
     * Insert User
     *
     * @return void
     */
    public function create_user($user_info, $role = 'participant')
    {
        $aData = [
            "email" => $user_info["email"],
            "username" => $user_info["email"],

            "password" => bcrypt($user_info["password"]),
            "verification_code" => sha1(time())
        ];
        $new = User::checkIfNew($user_info['email']);
        if ($new === false) {
            return response()->json(['error' => 'user exists'], 409);
        }
        $data = User::create($aData);

        $newuser = User::findorFail($data->id);
        $newuser->assignRole($role);

        if ($newuser) {
            return $newuser;
        }
        return false;
    }

    /**
     * check users change password code
     * @param Request $request
     * @return type
     */
    public function check_change_password_code(Request $request)
    {


        $token = $request->code;
        $passwordReset = PasswordReset::where('token', $token)
            ->first();
        $user = User::where("email", $passwordReset->email)->first();

        if (!$passwordReset || !$user)
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }
        $pCtrl = new ParticipantController();
        $step = $pCtrl->getUserStep($user);
        if (isset($step)) {
            $passwordReset->step = $step;
        }
        return response()->json($passwordReset);
    }

    /**
     * check registration verification code
     * @param Request $request
     * @return type
     */
    public function check_verification_code(Request $request)
    {

        $request_code = $request->only("code");
        if (empty($request['code'])) {
            return $this->sendError('Validation Error.', ['invalid input']);
        }
        $user = User::where("verification_code", $request_code)->first();

        if (!$user || !isset($user)) {
            return response()->json(['error' => 'code not found'], 400);
        }
        $user->load('participant');
        $user_participant = $user->participant;
        $user_participant_referrer = User::where("id", $user_participant->seed_id)->first();
        if ($user_participant_referrer) {
            $referrer_verified_friends = $user_participant_referrer->participant->verified_friends_count;
            if ($referrer_verified_friends > 1) {
                return response()->json(['status' => False, 'error' => 'verified friends limit reached'], 202);
            }
        }

        if ($user->email_verified_at == 'null') {
            $user->verified = true;
            return response()->json(['user' => $user], 200);
        }
        $user->email_verified_at = Carbon::now();
        // TODO - verif code remove on accept ? 
        //        $user->verification_code = '';
        $user->save();
        if ($user->hasRole('participant')) {
            if ($token = auth()->login($user)) {
                $aCtrl = new AuthController();
                return $aCtrl->respondWithToken($token, $user);
            }
        }
        return response()->json(['user' => $user], 200);
    }
}
