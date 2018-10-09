<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Models\User;

class UserController extends Controller
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
        $users = User::orderBy('updated_at', 'desc')
                     ->orderBy('created_at', 'asc')
                     ->get();

        return view('user.list')->with([
            'users' => $users,
        ]);
    }


    /**
     * ユーザー詳細
     * 
     * @return \Illuminate\Http\Response
     */
    public function detail( User $user )
    {
        return view('user.detail')->with([
            'user' => $user,
        ]);
    }

}
