<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Models\Operator;

class OperatorController extends Controller
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
     * 管理ユーザー一覧
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $operators = Operator::orderBy('updated_at', 'desc')
                         ->orderBy('created_at', 'asc')
                         ->get();

        return view('operator.list')->with([
            'operators' => $operators,
        ]);
    }


    /**
     * 管理ユーザー詳細
     * 
     * @return \Illuminate\Http\Response
     */
/*
    public function detail( Operator $operator )
    {
        return view('operator.detail')->with([
            'operator' => $operator,
        ]);
    }
*/

}
