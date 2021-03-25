<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Http\Controllers\BaseController;

class OmniController extends BaseController
{

    //
    /**
     * omni search omni
     * @param Request [model, active,sortOrder,filter,pageSize]
     * @return array
     *
     */
    public function omni(Request $request)
    {

        $modelstring = isset($request->all()['model']) ? $request->all()['model'] : 'users';
        $modelstring = rtrim(ucfirst($modelstring), "s");
        if ($modelstring == 'Payout' || $modelstring == 'Notification') {
            $modelstring = "ProjectParticipant";
        } else if ($modelstring == 'Warning') {
            $modelstring = "User";
        }
        $model_name = '\\App\\' . $modelstring;
        $model = new $model_name;
        $order_by = $request->all()['active'] ? $request->all()['active'] : 'id';
        if ($modelstring === 'Log') {
            $searchField = 'message';
        } else if ($modelstring === 'User') {
            $searchField = 'email';
            $with = 'participant';
        } else if ($modelstring === 'ProjectParticipant') {
            $searchField = 'safeid';
            $order_by = $request->all()['active'] ? $request->all()['active'] : 'id';
            $with = 'user';
        } else if ($modelstring === 'Participant') {
            $searchField = 'paypal_id';

            $order_by = $request->all()['active'] ? $request->all()['active'] : 'user_id';
            $with = 'user';
        } else {
            $searchField = 'id';
        }


        $order = $request->all()['sortOrder'] ? $request->all()['sortOrder'] : 'desc';
        $filter = $request->all()['filter'] ? $request->all()['filter'] : null;
        $pageSize = $request->all()['pageSize'] ? $request->all()['pageSize'] : 10;


        if ($filter) {

            $l = $model::orderBy($order_by, $order)
                ->where($searchField, 'LIKE', '%' . $filter . '%')
                ->paginate($pageSize);
            if ($with) {
                $l->load($with);
            }
        } else {
            $l = $model::orderBy($order_by, $order)
                ->paginate($pageSize);
            if (!empty($with)) {
                $l->load($with);
            }
        }

        return $this->sendResponse($l, 200);
    }
}
