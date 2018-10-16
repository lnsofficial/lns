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
    public function index( Request $request )
    {
        $login_id        = '';
        $summoner_id     = '';
        $summoner_name   = '';
        $sort            = 'id';
        $order           = 'asc';

        $query = User::query();

        if( $request->has('login_id') )
        {
            $login_id        = $request->input('login_id');
            $query->where('login_id', 'like', '%'.$login_id.'%');
        }
        if( $request->has('summoner_id') )
        {
            $summoner_id     = $request->input('summoner_id');
            $query->where('summoner_id', $summoner_id);
        }
        if( $request->has('summoner_name') )
        {
            $summoner_name   = $request->input('summoner_name');
            $query->where('summoner_name', 'like', '%'.$summoner_name.'%');
        }

        if( $request->has('sort') )
        {
            $sort = $request->input('sort');
        }
        if( $request->has('order') )
        {
            $order = $request->input('order');
        }
        $query->orderBy($sort, $order);

        // ランク情報、所属チーム情報も読み込んでおく
        $users = $query->with('ranks','member.team')->paginate(50);

        return view('user.list')->with([
            'users'         => $users,
            'login_id'      => $login_id,
            'summoner_id'   => $summoner_id,
            'summoner_name' => $summoner_name,
            'sort'          => $sort,
            'order'         => $order,
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
