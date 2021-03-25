<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProfileController extends BaseController {

    /**
     * Get the MOTD
     *
     * @return \Illuminate\Http\Response
     */
    public function motd(Request $request) {
        //
        if (!Auth::user()) {
            return $this->sendResponse('Not logged in', 'MOTD retrieved successfully.');
        }
        $role = Auth::user()->getRoleNames()[0];

        if ($role == 'researcher') {
            $message = DB::table('settings')->get()[0]->researchermessage;
        } else if ($role == 'participant') {
            $message = DB::table('settings')->get()[0]->participantmessage;
        } else if ($role == 'administrator') {
            $message = DB::table('settings')->get()[0]->adminmessage;
        }

        return $this->sendResponse($message, 'MOTD retrieved successfully.');
    }

    /**
     * Display a listing of the MOTD.
     *
     * @return \Illuminate\Http\Response
     */
    public function index() {
        //
    }

    /**
     * Show the form for creating a new MOTD.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        //
    }

    /**
     * Store a newly created MOTD in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request) {
        //
    }

    /**
     * Display the specified MOTD.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $user) {
        //
    }

    /**
     * Show the form for editing the specified MOTD.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user) {
        //
    }

    /**
     * Update the specified MOTD in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $user) {
        //
    }

    /**
     * Remove the specified MOTD from storage.
     *
     * @param  \App\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $user) {
        //
    }

}
