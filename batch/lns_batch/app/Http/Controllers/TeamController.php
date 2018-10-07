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
    public function index()
    {
        $teams = Team::orderBy('logo_updated_at', 'desc')
                     ->orderBy('created_at',      'asc')
                     ->get();

        return view('team.list')->with([
            'teams' => $teams,
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
