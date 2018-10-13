<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Libs\WorkLog;

class WorklogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth.admin');
    }

    /**
     * 作業ログ一覧
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $worklogs = WorkLog::getList();
//dd($worklogs);
        return view('worklog.list')->with([
            'worklogs'    => $worklogs,
        ]);
    }


    /**
     * 作業ログ詳細
     * 
     * @return \Illuminate\Http\Response
     */
/*
    public function detail( Worklog $worklog )
    {
        return view('worklog.detail')->with([
            'worklog' => $worklog,
        ]);
    }
*/

}
