<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Match.php' );
require_once( PATH_MODEL . 'Team.php' );
require_once( PATH_MODEL . 'LadderRanking.php' );
require_once( PATH_MODEL . 'League.php' );
require_once( PATH_MODEL . 'LoginAccount.php' );

class MatchController extends BaseController{
	const DISPLAY_DIR_PATH	= "Match";
	const DISPLAY_FILE_PATH	= "Match_common";

	public function __construct(){
	}

	public function display(){
		// TODO この辺共通処理に移動
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		// TODO REQUESTから取得、とりあえずスタティックに作る
		$iMatchId = $_REQUEST["match_id"];
		
		$oDb = new Db();
		
		$oMatch = new Match( $oDb, $iMatchId );
		
		$iHostTeamId = $oMatch->host_team_id;
		$oHostTeam = new Team( $oDb, $iHostTeamId );
		
		$oLoginAccount = new LoginAccount( $oDb, $_SESSION["id"] );
		
		$showJoin = false;
		$showCancel = false;
		$showRegsiterResult = false;
		switch( $oLoginAccount->team_id ){
			case $iHostTeamId:
				// ホスト
				switch( $oMatch->state ){
					case Match::MATCH_STATE_RECRUIT:
						// 募集中
						$showCancel = true;
						break;
					case Match::MATCH_STATE_MATCHED:
						// 試合結果登録待ち
						if( $oMatch->enableCancel() ){
							$showCancel = true;
						}
						$showRegsiterResult = true;
						break;
				}
				break;
			case $oMatch->apply_team_id:
				// ゲスト
				switch( $oMatch->state ){
					case Match::MATCH_STATE_RECRUIT:
						// 募集中
						$showJoin = true;
						break;
					case Match::MATCH_STATE_MATCHED:
						// 試合結果登録待ち
						if( $oMatch->enableCancel() ){
							$showCancel = true;
						}
						$showRegsiterResult = true;
						break;
				}
				break;
			default:
				// それ以外
				switch( $oMatch->state ){
					case Match::MATCH_STATE_RECRUIT:
						// 募集中
						if( date( 'Y-m-d H:i:s' ) < date( 'Y-m-d H:i:s', strtotime( $oMatch->match_date ) ) ){
							$showJoin = true;
						}
						break;
					case Match::MATCH_STATE_MATCHED:
						// 試合結果登録待ち
						break;
				}
				break;
				
		}
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "match_id", $iMatchId );
		$smarty->assign( "host_team_name", $oHostTeam->team_name );
		$smarty->assign( "match_date", $oMatch->match_date );
		$smarty->assign( "recruit_start_date", $oMatch->recruit_start_date );
		$smarty->assign( "stream", $oMatch->stream );
		$smarty->assign( "image_path", $oMatch->screen_shot_url );
		$smarty->assign( "type", $oMatch->type );
		$smarty->assign( "show_join", $showJoin );
		$smarty->assign( "show_cancel", $showCancel );
		$smarty->assign( "show_register_result", $showRegsiterResult );
		
		$smarty->display('Match/MatchDetail.tmpl');
	}

	public function join(){
		// TODO この辺共通処理に移動
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$iMatchId = intval( $_REQUEST["match_id"] );
		$oDb = new Db();
		$oLoginAccount = new LoginAccount( $oDb, $_SESSION["id"] );
		
		// マッチ情報取得
		$oMatch = new Match( $oDb, $iMatchId );
		
		// ステータスチェック（募集中以外ならエラー）
		if( $oMatch->state != Match::MATCH_STATE_RECRUIT ){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_NOT_RECRUITE );
			exit;
		}
		
		$oHostTeam = new Team( $oDb, $oMatch->host_team_id );
		$oApplyTeam = new Team( $oDb, $oLoginAccount->team_id );
		// TODO エラー処理
		// TODO その内チームに所属リーグの情報引っ張ってくる関数作成
		$oHostTeamLadder = $oHostTeam->getCurrentLadder( $oDb );
		$oApplyTeamLadder = $oApplyTeam->getCurrentLadder( $oDb );
		
		$oHostTeamLeague = new League( $oDb, $oHostTeamLadder->league_id );
		$oApplyTeamLeague = new League( $oDb, $oApplyTeamLadder->league_id );
		
		// ホストとゲストが同じチームはエラ－
		if( $oHostTeam->team_id == $oApplyTeam->team_id ){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_HOST_EQ_GUEST );
			exit;
		}
		
		switch( $oMatch->type ){
			case Match::MATCH_TYPE_ANY:
				// 何もしない
				break;
			case Match::MATCH_TYPE_LESS_SAME:
				// ホストのランクが自分のランクより下ならエラー
				if( $oHostTeamLeague->rank > $oApplyTeamLeague->rank ){
					self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_HOST_DONT_APPLY );
					exit;
				}
				break;
			case Match::MATCH_TYPE_LESS_ONE_ON_THE_SAME:
				// ホストのランクが自分のランクから2つ以下ならエラー
				if( $oHostTeamLeague->rank > $oApplyTeamLeague->rank + 1 ){
					self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_HOST_DONT_APPLY );
					exit;
				}
				break;
		}
		
		$oLatestLastJoin = $oApplyTeam->getLastJoin( $oDb );
		
		if( date( 'Y-m-d H:i:s' ) < date( 'Y-m-d H:i:s', strtotime( $oMatch->match_date . " - 1 day" ) ) ){
			// 現在日時が試合予定日時より1日以上前ならチェック
			if( $oLatestLastJoin ){
				$dtLastJoin = date($oLatestLastJoin->join_date);
				if( date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime($oLatestLastJoin->join_date . " + 5 day") ) ){
					if( date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime( $oMatch->recruit_start_date . " + 1 day") ) ){
						self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_REGIST_INTERVAL );
						exit;
					}
				}
			}
		}
		if( date( 'Y-m-d H:i:s' ) < date( 'Y-m-d H:i:s', strtotime( $oMatch->match_date ) ) ){
			$showJoin = true;
		}
		
		$oLastJoin = new LastJoin( $oDb );
		
		// トランザクション開始
		$oDb->beginTransaction();
		
		$oMatch->apply_team_id = $oApplyTeam->team_id;
		$oMatch->state = Match::MATCH_STATE_MATCHED;
		$oMatch->save();
		
		$oLastJoin->join_date = date('Y-m-d H:i:s');
		$oLastJoin->team_id = $oApplyTeam->team_id;
		$oLastJoin->match_id = $oMatch->id;
		$oLastJoin->state = LastJoin::STATE_ENABLE;
		$oLastJoin->save();
		
		$oDb->commit();
		
		self::displayCommonScreen( MSG_HEAD_MATCH_COMPLETE, MSG_MATCH_COMPLETE );
	}
	
	public function cancel(){
		// TODO この辺共通処理に移動
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$iMatchId = intval( $_REQUEST["match_id"] );
		
		$oDb = new Db();
		$oLoginAccount = new LoginAccount( $oDb, $_SESSION["id"] );
		$iCurTeamId = $oLoginAccount->team_id;
		
		// マッチ情報取得
		$oMatch = new Match( $oDb, $iMatchId );
		
		$oDb->beginTransaction();
		
		switch( $iCurTeamId ){
			case $oMatch->host_team_id:
				// キャンセルしたのがホストだったらキャンセルステータスに変更
				$oMatch->state = Match::MATCH_STATE_CANCEL;
				break;
			case $oMatch->apply_team_id:
				// キャンセルしたのがゲストだったら参加の取り消しのみ、募集は残す
				$oMatch->state = Match::MATCH_STATE_RECRUIT;
				// キャンセルしたのがゲストだったら参加履歴のテーブル更新
				$oApplyTeam = new Team( $oDb, $oMatch->apply_team_id );
				$oMatch->apply_team_id = 0;
				$oLastJoin = $oApplyTeam->getLastJoin( $oDb );
				$oLastJoin->state = LastJoin::STATE_CANCEL;
				$oLastJoin->save();
				break;
			default:
				// 試合のホスト・ゲスト以外がキャンセルしようとしたらエラー
				self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
				exit;
		}
		
		$oMatch->save();
		
		$oDb->commit();
		
		self::displayCommonScreen( MSG_HEAD_MATCH_CANCEL, MSG_MATCH_CANCEL );
	}
	
	public function recruitList(){
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$oDb = new Db();
		
		// TODO その内共通化
		$iState = $_REQUEST["search_option"]["state"];
		$ahsSearchOption = [];
		if( !empty( $iState ) ){
			$ahsSearchOption[] = [ "column" => "state",  "type" => "int", "value" => $iState ];
		}
		
		$sStartDate = $_REQUEST["search_option"]["start_date"];
		if( !empty( $sStartDate ) ){
			$ahsSearchOption[] = [ "column" => "match_date", "type" => "date", "operator" => ">=", "value" => $sStartDate ];
		}
		
		$sEndDate = $_REQUEST["search_option"]["end_date"];
		if( !empty( $sEndDate ) ){
			$ahsSearchOption[] = [ "column" => "match_date", "type" => "date", "operator" => "<=", "value" => $sEndDate ];
		}
		$oMatchList = Match::getMatchList( $oDb, $ahsSearchOption );
		
		$ahsMatchList = [];
		
		while( $row = $oMatchList->fetch_assoc() ) {
			$oHostTeam = new Team( $oDb, $row["host_team_id"] );
			$oHostLeague = $oHostTeam->getLeague( $oDb );
			
			$oApplyTeam = null;
			$oApplyLeague = null;
			if( $row["apply_team_id"] != 0 ){
				$oApplyTeam = new Team( $oDb, $row["apply_team_id"] );
				$oApplyLeague = $oApplyTeam->getLeague( $oDb );
			}
			
			$bNew = false;
			if( date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime( $row["recruit_start_date"] . " + 1 day") ) ){
				$bNew = true;
			}
			
			$ahsMatch = [];
			$ahsMatch["id"]					= $row["id"];
			$ahsMatch["new"]				= $bNew;
			$ahsMatch["state"]				= $row["state"];
			$ahsMatch["match_date"]			= date('n月j日 H:i', strtotime( $row["match_date"]) );
			$ahsMatch["host_team_id"]		= $row["host_team_id"];
			$ahsMatch["host_team_name"]		= $oHostTeam->team_name;
			$ahsMatch["host_league_name"]	= $oHostLeague->league_name;
			if( $oApplyTeam ){
				$ahsMatch["apply_team_id"]		= $row["apply_team_id"];
				$ahsMatch["apply_team_name"]	= $oApplyTeam->team_name;
				$ahsMatch["apply_league_name"]	= $oApplyLeague->league_name;
			}
			$ahsMatch["winner"]				= $row["winner"];
			
			$ahsMatchList[] = $ahsMatch;
		}
		
		$oLoginAccount = new LoginAccount( $oDb, $_SESSION["id"] );
		$oLoginTeam = new Team( $oDb, $oLoginAccount->team_id );
		$oLatestLastJoin = $oLoginTeam->getLastJoin( $oDb );
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "match_recruit_list"	, $ahsMatchList );
		$smarty->assign( "last_join_date"		, $oLatestLastJoin->join_date );
		$smarty->assign( "state"				, $iState );
		$smarty->assign( "start_date"			, $sStartDate );
		$smarty->assign( "end_date"				, $sEndDate );
		
		$smarty->display('Match/MatchRecruitList.tmpl');
	}
	
	public function commit(){
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		if(!self::validation()){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
			exit;
		}

		$oDb = new Db();
		$oLoginAccount = new LoginAccount( $oDb, $_SESSION["id"] );
		$this->checkRecruitEnable( $oLoginAccount->team_id );

		// DBに登録
		$oDb = new Db();
		$oDb->beginTransaction();
		
		$oMatch = new Match( $oDb );
		$oMatch->host_team_id		= $oLoginAccount->team_id;
		$oMatch->match_date			= $_REQUEST["match_date"];
		$oMatch->recruit_start_date	= date( 'Y-m-d H:i:s' );
		$oMatch->type				= $_REQUEST["type"];
		$oMatch->stream				= $_REQUEST["stream"];
		$oMatch->state				= Match::MATCH_STATE_RECRUIT;
		$oMatch->save();
		
		$oDb->commit();
		
		self::displayCommonScreen( MSG_HEAD_MATCH_COMPLETE, MSG_MATCH_RECRUIT_COMPLETE );
	}

	public function confirm(){
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		if(!self::validation()){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
			exit;
		}

		$oDb = new Db();
		$oLoginAccount = new LoginAccount( $oDb, $_SESSION["id"] );
		
		$this->checkRecruitEnable( $_REQUEST["match_date"], $oLoginAccount->team_id );
		
		$dtMatchDate = date( 'Y-m-d H:i:s', strtotime( $_REQUEST["match_date"] ) );

		self::displayMatchingConfirm($_REQUEST["type"], $dtMatchDate, $_REQUEST["stream"]);
	}

	public function checkRecruitEnable( $sMatchDate, $host_id ){
		// 4 regist in a month
		$count = $this->getMatchCountInMonth( $sMatchDate, $host_id );
		if ($count >= Match::MAX_MATCH_RECRUIT_COUNT) {
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_OVER_REGIST );
			exit;
		}
	}

	public function getMatchCountInMonth( $sMatchDate, $host_id ){
		// 4 regist in a month
		$start_month	= date("Y-m-01", strtotime( $sMatchDate ) );
		$end_month		= date("Y-m-01", strtotime( $sMatchDate . " +1 month"));
		
		$sSelectSql		= "SELECT count(*) as cnt FROM match_recruit_list WHERE host_team_id = ? and ? <= match_date and match_date < ? AND state <> ?";
		$ahsParameter	= [ $host_id, $start_month, $end_month, Match::MATCH_STATE_CANCEL ];
		
		$oDb = new Db();
		
		$result = $oDb->executePrepare( $sSelectSql, "issi", $ahsParameter );
		
		$row = $result->fetch_assoc();
		
		return $row["cnt"];
	}
	
	private function validation(){
		$bResult	= true;
		if(!$_REQUEST["type"]){
			$bResult = false;
		}
		if(!$_REQUEST["match_date"]){
			$bResult = false;
		} else {
			// 試合日時が現在日時より後の場合はエラー
			if( date( 'Y-m-d H:i:s' ) > date( 'Y-m-d H:i:s', strtotime( $_REQUEST["match_date"] ) ) ){
				$bResult = false;
			}
		}
		if(!isset($_REQUEST["stream"])){
			$bResult = false;
		}
		return $bResult;
	}

	public function form(){
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		self::displayMatchingForm();
	}

	public function displayMatchingForm(){
		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		$smarty->display('Match/MatchingForm.tmpl');
	}
	
	public function displayMatchingConfirm($type, $match_date, $stream){
		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;

		$smarty->assign("type",       $type);
		$smarty->assign("match_date", $match_date);
		$smarty->assign("stream",     $stream);

		$smarty->display('Match/MatchingForm_confirm.tmpl');
	}
}
