<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
//use App\Models\Notice;

class NoticeController extends Controller
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
     * お知らせ一覧
     * 
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
/*
        $notices = Notice::orderBy('updated_at', 'desc')
                         ->orderBy('created_at', 'asc')
                         ->get();

        return view('notice.list')->with([
            'notices' => $notices,
        ]);
*/
        return view('notice.list');
    }


    /**
     * お知らせ詳細
     * 
     * @return \Illuminate\Http\Response
     */
/*
    public function detail( Notice $notice )
    {
        return view('notice.detail')->with([
            'notice' => $notice,
        ]);
    }
*/

}
