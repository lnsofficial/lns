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
    public function index( Request $request )
    {
        $name            = '';
        $email           = '';
        $activate        = '';
        $sort            = 'id';
        $order           = 'asc';

        $query = Operator::query();

        if( $request->has('name') )
        {
            $name            = $request->input('name');
            $query->where('name', 'like', '%'.$name.'%');
        }
        if( $request->has('email') )
        {
            $email           = $request->input('email');
            $query->where('email', $email);
        }
        if( $request->has('activate') )
        {
            $activate        = intval($request->input('activate'));
            $query->where('activate', $activate);
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

        $operators = $query->paginate(50);

        return view('operator.list')->with([
            'operators'     => $operators,
            'name'          => $name,
            'email'         => $email,
            'activate'      => $activate,
            'sort'          => $sort,
            'order'         => $order,
        ]);
    }


    /**
     * 管理ユーザー詳細
     * 
     * @return \Illuminate\Http\Response
     */
    public function detail( Operator $operator )
    {
        return view('operator.detail')->with([
            'operator' => $operator,
        ]);
    }

}
