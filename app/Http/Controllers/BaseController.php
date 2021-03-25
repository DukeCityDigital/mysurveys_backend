<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller as Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BaseController extends Controller
{
    //

    /**
     *
     * @param Validator $array
     * @param type $field
     * @return type
     * TODO unused
     */
    public function valueOrNull($array, $field)
    {
        if (isset($array[$field])) {
            return $array[$field];
        }
        return null;
    }

    /**
     * Log to database
     * @param type
     * Log::emergency($message);
     *Log::alert($message);
     *Log::critical($message);
     *Log::error($message);
     *Log::warning($message);
     *Log::notice($message);
     *Log::info($message);
     *Log::debug($message);
     */
    public function logger($type, $message, $logdata = [])
    {
        if ($user = Auth::user()) {
            $logdata['id'] = $user->id;
        }
        Log::$type($message, $logdata);
    }

    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];
        if (!empty($errorMessages)) {
            $response['data'] = $errorMessages;
        }
        return response()->json($response, $code);
    }
}
