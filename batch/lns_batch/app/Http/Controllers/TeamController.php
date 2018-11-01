<?php

namespace App\Http\Controllers;

use Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;

use App\Libs\UtilTime;
use App\Libs\WorkLog;
use App\Models\Team;
use App\Models\Ladder;
use App\Models\LnsDB;
use App\Models\Match;
use App\Models\TeamOwner;
use App\Models\TeamMember;
use App\Models\TeamStaff;
use App\Models\TeamContact;
use App\Models\UserTeamApply;
use App\Models\TeamJoin;
use App\Models\MatchCheckin;

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
        // チームメンバーレコードもランクまで含めて読み込んでおく
        $team->load('members.user.ranks');
        // ランキング情報も読み込んでおく
        $team->load(['ladders'=> function($query) {
            $query->with('league');
            $query->where('season', Ladder::SEASON_NOW);
            $query->orderBy('term', 'desc');
        }]);

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
        WorkLog::log( Auth::user(), "チームロゴ検閲状況を更新", $team->toArray() );

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
            $request->file('logo')->storeAs('public/logo/modified', $team->logoFileName());

            // レコードのロゴ更新日時も更新
            $team->logo_updated_at = UtilTime::now();
            $team->save();

            // ログに記録
            WorkLog::log( Auth::user(), "修正版チームロゴを更新", $team->toArray() );

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


    /**
     * チーム解散させる
     * 
     * @return \Illuminate\Http\Response
     */
    public function breakup( Request $request, Team $team )
    {
        // MATCH_STATE_MATCHED のマッチが無ければOKかな？
        $matched_matches = Match::where('state', Match::MATCH_STATE_MATCHED)
                                ->hostOrApply( $team->id )
                                ->get();
        if( ! $matched_matches->isEmpty() )
        {
            // 結果待ちのマッチングがあるので解散だめ。
            $request->session()->flash('error', '結果待ちのマッチングがあるので解散させられない。');
            return redirect()->back();
        }

        // 作業ファイル名 team_breakup_[YmdHis]_[teams.id]_[tablename].log
        $backup_filename_format = storage_path('backup/db') . '/' . 'team_breakup_' . date("YmdHis") . '_%d_%s.log';

        // 該当レコードを一応保存しておく、基本ユニークファイル名になるはずだから存在チェックはスルー！
        File::put(sprintf($backup_filename_format, $team->id, (new Team)         ->getTable()), Team         ::where('id',      $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new TeamOwner)    ->getTable()), TeamOwner    ::where('team_id', $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new TeamMember)   ->getTable()), TeamMember   ::where('team_id', $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new TeamStaff)    ->getTable()), TeamStaff    ::where('team_id', $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new TeamContact)  ->getTable()), TeamContact  ::where('team_id', $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new TeamJoin)     ->getTable()), TeamJoin     ::where('team_id', $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new Match)        ->getTable()), Match        ::hostOrApply($team->id)     ->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new MatchCheckin) ->getTable()), MatchCheckin ::where('team_id', $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new Ladder)       ->getTable()), Ladder       ::where('id',      $team->id)->get()->toJson());
        File::put(sprintf($backup_filename_format, $team->id, (new UserTeamApply)->getTable()), UserTeamApply::where('team_id', $team->id)->whereNull('deleted_at')->get()->toJson());

        // 懸念点：
        // このあとのトランザクションでrollbackかかった場合、↑のファイルだけ残る(頻度は多くないと思う)
        // 保存したレコードから復元するのも作っておいたほうがいい？と思ったけど、解散後userが他チームに既に所属済みだったりした場合、復元できない。
        // そもそも復元自体稀なのとこのバックアップ自体も念のため程度なのでその時は手動対応で。
        // 参考：TeamMember::insert( json_decode(File::get("filename"), true) );

        try
        {
            LnsDB::beginTransaction();

            // ここから削除
            Team         ::where('id',      $team->id)->delete();
            TeamOwner    ::where('team_id', $team->id)->delete();
            TeamMember   ::where('team_id', $team->id)->delete();
            TeamStaff    ::where('team_id', $team->id)->delete();
            TeamContact  ::where('team_id', $team->id)->delete();
            TeamJoin     ::where('team_id', $team->id)->delete();
            Match        ::hostOrApply($team->id)     ->delete();
            MatchCheckin ::where('team_id', $team->id)->delete();
            Ladder       ::where('team_id', $team->id)->delete();

            UserTeamApply::where('team_id', $team->id)
                         ->whereNull('deleted_at')
                         ->update(['deleted_at'=>UtilTime::now()]);

            LnsDB::commit();
        }
        catch( Exception $e )
        {
            // DB更新で失敗したならしょうがない・・・
            LnsDB::rollBack();

            $request->session()->flash('error', $e->getMessage());
            return redirect()->back();
        }


        // ログに記録
        WorkLog::log( Auth::user(), "チームを解散", $team->toArray() );
        $request->session()->flash('success', 'チーム['.$team->team_name.']['.$team->team_tag.']を解散しました');

        return redirect()->route('team.list');
    }

}
