<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\User;
use App\Notifications\EmailChangeNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use App\EmailChanges;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class EmailChangeController extends BaseController
{
    /*
      |--------------------------------------------------------------------------
      | Email Change Controller
      |--------------------------------------------------------------------------
      |
      | This controller allows the user to change his email address after he
      | verifies it through a message delivered to the enew email address.
      | This uses a temporarily signed url to validate the email change.
      |
     */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Only the authenticated user can change its email, but he should be able
        // to verify his email address using other device without having to be
        // authenticated. This happens a lot when they confirm by phone.
        //        TODO uncomment and lockdown re above?
        $this->middleware('auth')->only('change');
        // A signed URL will prevent anyone except the User to change his email.
        //        $this->middleware('signed')->only('verify');
    }

    /**
     * Changes the user Email Address for a new one
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function change_email_request(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 400);
        }

        $token = (string) Str::uuid();
        $user = User::find(Auth::user()->id);
        $emailChange = EmailChanges::updateOrCreate(
            ['email' => $request->all()['email']],
            [
                'email' => $user->email,
                'new_email' => $request->all()['email'],
                'token' => $token
            ]
        );
        $message = 'An email change request has been received.  Please check your new email';

        if ($user && $emailChange) {
            $this->logger("info", 'User email change.' . $user->email, $validator->valid());
            Notification::route('mail', $request->email)
                ->notify(new EmailChangeNotification(Auth::user()->id, $request->all()['email']));
            $message = $message;
        }
        return response()->json(["message" => $message], 200);
    }

    /**
     * Verifies and completes the Email change
     *
     * @param Request $request
     * @param User $user
     * @param string $email
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function change_email_verify(Request $request)
    {
        /**
         * TODO lock this function down
         */
        $request->validate([
            //            'email' => 'required|email|unique:users',
            'token' => 'required'
        ]);

        $user = User::find(Auth::user()->id);
        $token = $request->token;
        $desired_email = EmailChanges::where("token", $token)->first();
        if (!$desired_email)
            return $this->sendError('Validation Error.', ['invalid input']);
        if (Carbon::parse($desired_email->updated_at)->addMinutes(720)->isPast()) {
            $desired_email->delete();
            return $this->sendError('Validation Error.', ['expired token']);
        }
        // Change the Email
        $user->update([
            'email' => $desired_email->new_email
        ]);
        $user->save();
        $desired_email->delete();

        if ($token = auth()->login($user)) {
            $aCtrl = new AuthController();
            return $aCtrl->respondWithToken($token, $user);
        }
    }
}
