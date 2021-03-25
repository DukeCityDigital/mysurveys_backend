<?php

namespace App\Http\Controllers;

use App\Http\Controllers\BaseController;
use App\Log;
use Illuminate\Http\Request;

class LogController extends BaseController
{

    /**
     * log Retrieve log
     * @param Request [active,sortOrder,filter,pageSize]
     * @return array[Log]
     * TODO: error code / log type
     */
    public function logs(Request $request)
    {
        $order_by = $request->all()['active'] ? $request->all()['active'] : 'id';
        $order = $request->all()['sortOrder'] ? $request->all()['sortOrder'] : 'desc';
        $filter = $request->all()['filter'] ? $request->all()['filter'] : null;
        $pageSize = $request->all()['pageSize'] ? $request->all()['pageSize'] : 30;
        if ($filter) {
            $l = Log::orderBy($order_by, $order)
                ->where('message', 'LIKE', '%' . $filter . '%')
                ->paginate($pageSize);
        } else {
            $l = Log::orderBy($order_by, $order)
                ->paginate($pageSize);
        }
        return $this->sendResponse($l, 200);
    }
}
