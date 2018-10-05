<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
		// チーム一覧
		$teams = Team::orderBy('logo_updated_at', 'desc')
					 ->orderBy('created_at',      'asc')
					 ->get();

        return view('team.list')->with([
			'teams' => $teams,
		]);
    }


    /**
     * 
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
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function update( Request $request, Team $team )
    {
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
/*
		$this->validate($request, [
		    'logo_status' => 'required|numeric|min:0|max:3'
		]);
		if( $request->input('logo_status')->isValid() )
		{
*/


			$team->logo_status     = $input['logo_status'];
			$team->logo_updated_at = UtilTime::now();
			$team->save();

			return redirect()
				->route('team.detail', ['team'=>$team->id])
				->with('success', 'チームロゴ状況を更新しました。');

    }


    /**
     * 
     *
     * @return \Illuminate\Http\Response
     */
    public function logoUpdate( Request $request, Team $team )
    {
		$this->validate($request, [
		    'logo' => 'image|mimes:png|max:1000|dimensions:max_width=500,max_height=500'
		]);
		if( $request->file('logo')->isValid() )
		{
			$request->file('logo')->storeAs('public/logo/modified', $team->id.'_logo.png');

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
