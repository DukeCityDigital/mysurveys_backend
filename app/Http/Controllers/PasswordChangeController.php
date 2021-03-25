<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PasswordChangeController extends BaseController
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Change password request
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function change_password_request(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required',
            'new_password' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json($validator->messages()->toArray(), 400);
        }

        $user = User::find(Auth::user()->id);
        $credentials = [
            "email" => $user->email,
            "password" => $request->all()['password']
        ];
        if (!$token = auth()->attempt($credentials)) {
            return $this->sendError("Passwords don't match", 400);
        }

        if ($request->all()['password'] == $request->all()['new_password']) {
            return $this->sendError("Your new password should be different than your current password", 400);
        }
        $user->verification_code = "";
        $user->password = \Hash::make($request->all()['new_password']);
        $user->changed_pw = 1;

        $user->save();
        return $this->sendResponse($user, 'Password updated successfully');
    }
}
