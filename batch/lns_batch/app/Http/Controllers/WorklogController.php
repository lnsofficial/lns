<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;

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
     * ユーザー一覧
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('worklog.list');
    }


    /**
     * ユーザー詳細
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
