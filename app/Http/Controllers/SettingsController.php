<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Settings;
use App\Http\Resources\Settings as SettingsResource;
use App\Http\Controllers\BaseController;
use Validator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

class SettingsController extends BaseController
{

    /**
     * Display a listing of the Settings.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $settings = Settings::first();
        return $this->sendResponse($settings->toArray(), 'Settings retrieved successfully.');
    }

    /**
     * Store a newly created Settings in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function backup(Request $request)
    {
        $this->logger('info', $request->user()->email . ' Requested a database backup', $request->user()->toArray());

        Artisan::call("backup:run");
        $s = Settings::first();
        $s->adminmessage = "Database backed up on " . date("Y-m-d:H:i:s");
        $this->logger('info', $request->user()->email . ' Database backup processed', $request->user()->toArray());

        $s->save();
        return $this->sendResponse("Request processed.  Check system email " . date("Y-m-d"), 'Settings created successfully.');
    }

    /**
     * Store a newly created Settings in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, Settings::validator());
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $settings = Settings::create($input);
        return $this->sendResponse(new SettingsResource($settings), 'Settings created successfully.');
    }

    /**
     * Display the specified Settings.
     *
     * @param  \App\settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $setting = Settings::find($id);
        if (is_null($setting)) {
            return $this->sendError('Setting not found.');
        }
        return $this->sendResponse(new SettingsResource($setting), 'Settings retrieved successfully.');
    }

    /**
     * Update the specified Settings in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\settings  $settings
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Settings $settings, $id)
    {

        $input = $request->all();
        $setting = $settings::findOrFail($id);
        $validator = Validator::make($input, Settings::validator());
        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $setting->update($validator->valid());
        $this->logger('info', $request->user()->email . ' updated settings', $validator->valid());
        return $this->sendResponse(new SettingsResource($setting), 'Setting updated successfully.');
    }

    /**
     * Remove the specified Settings from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Settings::findOrFail($id)->delete();
        return $this->sendResponse([], 'Setting deleted');
    }
}
