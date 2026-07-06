<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Facades\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->botUser;
        $page = $request->get('page', 1);
        $perPage = $request->get('per_page', 20);

        $logs = UserLog::getLogs($user->id, $perPage, $page);

        return response()->json($logs);
    }
}
