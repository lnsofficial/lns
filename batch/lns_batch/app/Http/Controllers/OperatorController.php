<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Libs\WorkLog;
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


    /**
     * 管理ユーザー更新(今のところactivate変更)
     * 
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, Operator $operator )
    {
        // 今のところ activate しか、受け取ってvalidationしてないけど他も受け取れば更新可
        $input = $request->all();
        $validator = Validator::make($input, [
            'activate' => 'required|numeric|min:'.Operator::ACTIVATE_STATUS_UNAUTHENTICATED.'|max:'.Operator::ACTIVATE_STATUS_AUTHENTICATED
        ]);
        if ($validator->fails())
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors( $validator );
        }

        // 更新
        $operator->activate     = $input['activate'];
        $operator->save();

        // ログに記録
        WorkLog::log( Auth::user(), "管理ユーザーのactivateを更新", $operator->toArray() );

        return redirect()
            ->route('operator.update', ['operator'=>$operator->id])
            ->with('success', 'activateを更新しました。');

    }

}
