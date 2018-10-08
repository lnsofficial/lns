<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Models\Team;

class TeamController extends Controller
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
     * チーム一覧
     * 
     * @return \Illuminate\Http\Response
     */
    public function index( Request $request )
    {
        $team_name   = $request->input('team_name');
        $team_tag    = $request->input('team_tag');
        $logo_status = '';
        $sort        = 'id';
        $order       = 'asc';

        $query = Team::query();

        if( $request->has('team_name') )
        {
            $team_name   = $request->input('team_name');
            $query->where('team_name', 'like', '%'.$team_name.'%');
        }
        if( $request->has('team_tag') )
        {
            $team_tag    = $request->input('team_tag');
            $query->where('team_tag', 'like', '%'.$team_tag.'%');
        }
        if( $request->has('logo_status') )
        {
            $logo_status = intval($request->input('logo_status'));
            $query->where('logo_status', $logo_status);
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

        $teams = $query->paginate(50);

        return view('team.list')->with([
            'teams'       => $teams,
            'team_name'   => $team_name,
            'team_tag'    => $team_tag,
            'logo_status' => $logo_status,
            'sort'        => $sort,
            'order'       => $order,
        ]);
    }


    /**
     * チーム詳細
     * 
     * @return \Illuminate\Http\Response
     */
    public function detail( Team $team )
    {
        return view('team.detail')->with([
            'team' => $team,
        ]);
    }


    /**
     * チーム情報更新(今のところロゴ検閲)
     * 
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, Team $team )
    {
        // 今のところ logo_status しか、受け取ってvalidationしてないけど他も受け取れば更新可
        $input = $request->all();
        $validator = Validator::make($input, [
            'logo_status' => 'required|numeric|min:0|max:3'
        ]);
        if ($validator->fails())
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors( $validator );
        }

        // 更新
        $team->logo_status     = $input['logo_status'];
        $team->logo_updated_at = UtilTime::now();
        $team->save();

        // ログに記録
        \Log::notice( "[".Auth::user()->id.":".Auth::user()->name."] チームロゴ検閲状況を更新 ", $team->toArray() );

        return redirect()
            ->route('team.detail', ['team'=>$team->id])
            ->with('success', 'チームロゴ状況を更新しました。');

    }


    /**
     * 修正版チームロゴアップロード
     * 
     * @return \Illuminate\Http\Response
     */
    public function logoUpdate( Request $request, Team $team )
    {
        $this->validate($request, [
            'logo' => 'image|mimes:png|max:1000|dimensions:max_width=512,max_height=512'
        ]);
        if( $request->file('logo')->isValid() )
        {
            $request->file('logo')->storeAs('public/logo/modified', $team->id.'_logo.png');

            // レコードのロゴ更新日時も更新
            $team->logo_updated_at = UtilTime::now();
            $team->save();

            // ログに記録
            \Log::notice( "[".Auth::user()->id.":".Auth::user()->name."] 修正版チームロゴを更新 ", $team->toArray() );

            return redirect()
                ->route('team.detail', ['team'=>$team->id])
                ->with('success', '修正版ロゴを保存しました。');
        }
        else
        {
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['logo' => '画像がアップロードされていないか不正なデータです。']);
        }
    }

}
