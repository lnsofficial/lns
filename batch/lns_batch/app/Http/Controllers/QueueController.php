<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\ApiQueue;
use App\Libs\WorkLog;

class QueueController extends Controller
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
     * キュー一覧
     * 
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {
        $action          = '';
        $state           = '';
        $priority        = '';

        $sort            = 'id';
        $order           = 'asc';

        $query = ApiQueue::query();

        if( $request->has('action') )
        {
            $action          = intval($request->input('action'));
            $query->where('action', $action);
        }
        if( $request->has('state') )
        {
            $state           = intval($request->input('state'));
            $query->where('state', $state);
        }
        if( $request->has('priority') )
        {
            $priority        = $request->input('priority');
            $query->where('priority', $priority);
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
        $queues = $query->paginate(50);

        return view('queue.list')->with([
            'queues'        => $queues,
            'action'        => $action,
            'state'         => $state,
            'priority'      => $priority,
            'sort'          => $sort,
            'order'         => $order,
        ]);
    }


    /**
     * キュー詳細
     * 
     * @return \Illuminate\Http\Response
     */
    public function detail( ApiQueue $queue )
    {
        return view('queue.detail')->with([
            'queue' => $queue,
        ]);
    }


    /**
     * キュー情報更新(今のところstate)
     * 
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, ApiQueue $queue )
    {
        // 今のところ state しか、受け取ってvalidationしてないけど他も受け取れば更新可
        $input = $request->all();
        $validator = Validator::make($input, [
            'state' => 'required|numeric|min:0|max:3'
        ]);
        if ($validator->fails())
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors( $validator );
        }

        // 更新
        $queue->state     = $input['state'];
        $queue->save();

        // ログに記録
        WorkLog::log( Auth::user(), "Apiキュー:stateを更新", $queue->toArray() );

        return redirect()
            ->route('queue.detail', ['queue'=>$queue->id])
            ->with('success', 'Apiキュー:stateを更新しました。');

    }

}
