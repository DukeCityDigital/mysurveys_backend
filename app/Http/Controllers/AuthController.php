<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\User;
use App\Participant;

class AuthController extends BaseController
{

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('auth:api', ['except' => ['login']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        if ($token = auth()->attempt($credentials)) {
            $this->logger('info', 'User login: ' . $credentials['email'], $credentials);
            $user = User::with('participant')->where("email", $credentials['email'])->first();
            $banned = $user->banned;
            if ($banned) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            return $this->respondWithToken($token, $user);
        }
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh(), $this->guard()->user());
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }



    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function respondWithToken($token, $user)
    {
        // echo 'parti';
        // exit;
        $roleName = $user->roles()->first()->name;
        if (!$user->hasVerifiedEmail()) {
            $json = [
                'access_token' => $token,
                'email' => $user->email,
                'token_type' => 'bearer',
                'expires_in' => $this->guard()->factory()->getTTL() * 60,
                'mustVerifyEmailAddress' => true,
                'step' => 'verify'
            ];
        } else {

            if ($roleName === 'participant') {
                $pCtrl = new ParticipantController();
                $p = Participant::where("user_id", $user->id)->first();
                //                $friends = $p && $p->friends ? $p->friends : [];
                $friends = $p->friends;
                $friends->load("user");
                $step = $pCtrl->getUserStep();


                $json = [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $this->guard()->factory()->getTTL() * 60,
                    'role' => $roleName,
                    'subrole' => $user->subrole,
                    'id' => $user->id,
                    'is_seed' => $user->subrole,
                    'friends' => $friends,
                    'step' => $step
                ];
            } else {
                $json = [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => $this->guard()->factory()->getTTL() * 60,
                    'role' => $roleName,
                    'id' => $user->id,
                ];
            }
        }

        return response()->json($json);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
