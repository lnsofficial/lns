<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Models\Match;

class MatchController extends Controller
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
        $matches = Match::orderBy('updated_at', 'desc')
                        ->orderBy('created_at', 'asc')
                        ->get();

        return view('match.list')->with([
            'matches' => $matches,
        ]);
    }


    /**
     * ユーザー詳細
     * 
     * @return \Illuminate\Http\Response
     */
/*
    public function detail( Match $match )
    {
        return view('match.detail')->with([
            'match' => $match,
        ]);
    }
*/

}
