<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Match.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'Ladder.php' );
require_once( PATH_MODEL . 'TeamJoin.php' );
require_once( PATH_MODEL . 'League.php' );
require_once( PATH_MODEL . 'MatchCheckin.php' );

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
		$oHostTeam = new Teams( $oDb, $iHostTeamId );
		$oHostTeamLadder = $oHostTeam->getCurrentLadder( $oDb );
		$oHostTeamLeague = new League( $oDb, $oHostTeamLadder->league_id );
		
		$oLoginUser = new User( $oDb, $_SESSION["id"] );
		$oLoginTeam = $oLoginUser->getTeam();
		$oLoginTeamLadder = null;
		$oLoginTeamLeague = null;
		if( $oLoginTeam ){
		    $oLoginTeamLadder = $oLoginTeam->getCurrentLadder( $oDb );
    		if( $oLoginTeamLadder ){
    		    $oLoginTeamLeague = new League( $oDb, $oLoginTeamLadder->league_id );
    		}
		}

        $showJoin           = false;
        $showCheckin        = false; // チェックインできるのはとりあえずteam_memberのみで。
        $showCancel         = false;
        $showRegsiterResult = false;
		
		// TODO 確認とかも権限処理必要
		$bAuthorized = false;
		$ahsAuthorizedTeamInfo = $oLoginUser->getAuthorizedTeam();
		
		foreach( $ahsAuthorizedTeamInfo as $asTeamInfo ){
		    if( $oLoginTeam->id == $asTeamInfo["id"] ){
		        $bAuthorized = true;
		        break;
		    }
		}
		
		if( $bAuthorized && $oLoginTeamLadder ){
		    switch( $oLoginTeam->id ){
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
		    					$showJoin = $oMatch->enableJoin( $oHostTeamLeague->rank, $oLoginTeamLeague->rank );
		    				}
		    				break;
		    			case Match::MATCH_STATE_MATCHED:
		    				// 試合結果登録待ち
		    				break;
		    		}
		    		break;
		    		
		    }
		}

        // ・自身がこのマッチのhost_team_id/apply_team_idどちらかのチームのteam_ownerであること
        // ・このマッチのstateがMatch::MATCH_STATE_MATCHEDであること
        // この２つを満たしてるとき、$showCheckin = true にする。
        if( $oMatch->state == Match::MATCH_STATE_MATCHED && !empty($oLoginTeam) )
        {
            $user = User::info( $oLoginUser->id );
            $owner_team_ids = array_map( function($item){ return $item['team_id']; }, $user['team_owners'] );
            if( in_array($oMatch->host_team_id, $owner_team_ids) || in_array($oMatch->apply_team_id, $owner_team_ids) )
            {
                $showCheckin = true;
            }
        }


		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
        $smarty->assign( "match_id",             $iMatchId );
        $smarty->assign( "host_team_name",       $oHostTeam->team_name );
        $smarty->assign( "match_date",           $oMatch->match_date );
        $smarty->assign( "recruit_start_date",   $oMatch->recruit_start_date );
        $smarty->assign( "stream",               $oMatch->stream );
        $smarty->assign( "image_path",           $oMatch->screen_shot_url );
        $smarty->assign( "type",                 $oMatch->type );
        $smarty->assign( "show_join",            $showJoin );
        $smarty->assign( "show_checkin",         $showCheckin );
        $smarty->assign( "show_cancel",          $showCancel );
        $smarty->assign( "show_register_result", $showRegsiterResult );
		
		$smarty->display('Match/MatchDetail.tmpl');
	}

	public function join(){
		// TODO この辺共通処理に移動
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$iMatchId = intval( $_REQUEST["match_id"] );
		$oDb = new Db();
		
		// マッチ情報取得
		$oMatch = new Match( $oDb, $iMatchId );
		
		// ステータスチェック（募集中以外ならエラー）
		if( $oMatch->state != Match::MATCH_STATE_RECRUIT ){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_NOT_RECRUITE );
			exit;
		}
		
		$oLoginUser = new User( $oDb, $_SESSION["id"] );
		$oLoginTeam = $oLoginUser->getTeam();
		
		$oHostTeam = new Teams( $oDb, $oMatch->host_team_id );
		$oApplyTeam = new Teams( $oDb, $oLoginTeam->id );
		// TODO エラー処理
		// TODO その内チームに所属リーグの情報引っ張ってくる関数作成
		$oHostTeamLadder = $oHostTeam->getCurrentLadder( $oDb );
		$oApplyTeamLadder = $oApplyTeam->getCurrentLadder( $oDb );
                // 申請側チームが「大会に参加」を押していないと、laddersに登録されていない。この場合はだめ！
                if( empty($oApplyTeamLadder) )
                {
                    self::displayCommonScreen( ERR_HEAD_COMMON, "「大会に参加」後に試合募集への応募をしてください。" );
                    exit;
                }
		$oHostTeamLeague = new League( $oDb, $oHostTeamLadder->league_id );
		$oApplyTeamLeague = new League( $oDb, $oApplyTeamLadder->league_id );
		
		// ホストとゲストが同じチームはエラ－
		if( $oHostTeam->id == $oApplyTeam->id ){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_HOST_EQ_GUEST );
			exit;
		}
		
		
		$bEnableJoin = $oMatch->enableJoin( $oHostTeamLeague->rank, $oApplyTeamLeague->rank );
		if( !$bEnableJoin ){
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_HOST_DONT_APPLY );
			exit;
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
		
		$oTeamJoin = new TeamJoin( $oDb );
		
		// トランザクション開始
		$oDb->beginTransaction();
		
		$oMatch->apply_team_id = $oApplyTeam->id;
		$oMatch->state = Match::MATCH_STATE_MATCHED;
		$oMatch->save();
		
		$oTeamJoin->joined_at = date('Y-m-d H:i:s');
		$oTeamJoin->team_id = $oApplyTeam->id;
		$oTeamJoin->match_id = $oMatch->id;
		$oTeamJoin->state = TeamJoin::STATE_ENABLE;
		$oTeamJoin->save();
		
		$oDb->commit();
		
		self::displayCommonScreen( MSG_HEAD_MATCH_COMPLETE, MSG_MATCH_COMPLETE );
	}
	
	public function cancel(){
		// TODO この辺共通処理に移動
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$iMatchId = intval( $_REQUEST["match_id"] );
		
		$oDb = new Db();
		$oLoginUser = new User( $oDb, $_SESSION["id"] );
		$oLoginTeam = $oLoginUser->getTeam();
		$iCurTeamId = $oLoginTeam->id;
		
		// マッチ情報取得
		$oMatch = new Match( $oDb, $iMatchId );
		
		$oDb->beginTransaction();
		$oApplyTeamId = $oMatch->apply_team_id;
		
		switch( $iCurTeamId ){
			case $oMatch->host_team_id:
				// キャンセルしたのがホストだったらキャンセルステータスに変更
				$oMatch->state = Match::MATCH_STATE_CANCEL;
				break;
			case $oMatch->apply_team_id:
				// キャンセルしたのがゲストだったら参加の取り消しのみ、募集は残す
				$oMatch->state = Match::MATCH_STATE_RECRUIT;
				$oMatch->apply_team_id = 0;
				break;
			default:
				// 試合のホスト・ゲスト以外がキャンセルしようとしたらエラー
				self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
				exit;
		}
		
		$oMatch->save();
		
		// 参加者が居れば履歴のテーブル更新
		if( $oApplyTeamId ){
			$oApplyTeam = new Team( $oDb, $oApplyTeamId );
			$oLastJoin = $oApplyTeam->getLastJoin( $oDb );
			$oLastJoin->state = LastJoin::STATE_CANCEL;
			$oLastJoin->save();
		}
		
		$oDb->commit();
		
		self::displayCommonScreen( MSG_HEAD_MATCH_CANCEL, MSG_MATCH_CANCEL );
	}
	
	public function recruitList(){
		session_set_save_handler( new MysqlSessionHandler() );
		require_logined_session();
		
		$oDb = new Db();
		
		$ahsSearchOption = [];
		
		// TODO その内共通化
		if( isset( $_REQUEST["search_option"] ) ){
			$iState = $_REQUEST["search_option"]["state"];
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
		}
		
		$oMatchList = Match::getMatchList( $oDb, $ahsSearchOption );
		
		$ahsMatchList = [];
		
		while( $row = $oMatchList->fetch_assoc() ) {
			$oHostTeam = new Teams( $oDb, $row["host_team_id"] );
			$oHostLeague = $oHostTeam->getLeague( $oDb );
			
			$oApplyTeam = null;
			$oApplyLeague = null;
			if( $row["apply_team_id"] != 0 ){
				$oApplyTeam = new Teams( $oDb, $row["apply_team_id"] );
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
		
		$oUser = new User( $oDb, $_SESSION["id"] );
		$oLoginTeam = $oUser->getTeam();
		
		$bJoinedLadder = false;
		$ahsAuthorizedTeamInfo = null;
		$oLatestLastJoin = null;
		if( $oLoginTeam ){
		    $oLatestLastJoin = $oLoginTeam->getLastJoin( $oDb );
		    $ahsUserInfo = User::info( $oUser->id );
		    
		    $ahsAuthorizedTeamInfo = $oUser->getAuthorizedTeam();
		    
		    foreach( $ahsAuthorizedTeamInfo as $asTeamInfo ){
		        if( $asTeamInfo["ladder"] ){
		            $bJoinedLadder = true;
		            break;
		        }
		    }
		}
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "match_recruit_list"	, $ahsMatchList );
		$smarty->assign( "is_joined_ladder"	, $bJoinedLadder );
		if( $oLatestLastJoin ){
			$smarty->assign( "last_join_date"		, $oLatestLastJoin->joined_at );
		}
		if( isset( $iState ) ){
			$smarty->assign( "state"				, $iState );
		}
		if( isset( $sStartDate ) ){
			$smarty->assign( "start_date"			, $sStartDate );
		}
		if( isset( $sEndDate ) ){
			$smarty->assign( "end_date"				, $sEndDate );
		}
		
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
		$oUser = new User( $oDb, $_SESSION["id"] );
		$oLoginTeam = $oUser->getTeam();
		$this->checkRecruitEnable( $_REQUEST["match_date"], $oLoginTeam->id );

		// DBに登録
		$oDb = new Db();
		$oDb->beginTransaction();
		
		$oMatch = new Match( $oDb );
		$oMatch->host_team_id		= $oLoginTeam->id;
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
		$oUser = new User( $oDb, $_SESSION["id"] );
		$oLoginTeam = $oUser->getTeam();
		
		$this->checkRecruitEnable( $_REQUEST["match_date"], $oLoginTeam->id );
		
		$dtMatchDate = date( 'Y-m-d H:i:s', strtotime( $_REQUEST["match_date"] ) );

		self::displayMatchingConfirm($_REQUEST["type"], $dtMatchDate, $_REQUEST["stream"]);
	}

	public function checkRecruitEnable( $sMatchDate, $host_id ){
		// 5 regist in a month
        $count = Match::getMatchCountAtMonthByDate( $host_id, $sMatchDate, true);
		if ($count >= Match::MAX_MATCH_RECRUIT_COUNT) {
			self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_OVER_REGIST );
			exit;
		}
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


    /**
     * 試合へのチェックインするやつ(form)
     * 
     */
    public function checkin_form()
    {
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

        $db = new Db();

        // 試合情報とチェックイン情報を取ってきてsmartyに渡す。
        $match_id = $_REQUEST['match_id'];
        $match    = new Match( $db, $match_id );

        // 適当なmatch_idはNG。
        if( empty($match) )
        {
            self::displayCommonScreen( ERR_HEAD_COMMON, '試合がみつかりません。' );
            exit;
        }

        // このマッチのhost_team_id/apply_team_idどちらかの、team_ownerじゃないとNG。
        $user         = User::info( $_SESSION["id"] );
        $owner_team_ids = array_map( function($item){ return $item['team_id']; }, $user['team_owners'] );
        if( empty($user['team']) || (!in_array($match->host_team_id, $owner_team_ids) && !in_array($match->apply_team_id, $owner_team_ids)) )
        {
            self::displayCommonScreen( ERR_HEAD_COMMON, 'この試合のチームの代表者である必要があります。' );
            exit;
        }

        // team_membersにcheckinsの情報付け足す。
        $checkins     = MatchCheckin::getByMatchIdTeamId( $match_id, $user['team']['id'] );
        $team_members = TeamMembers::getByTeamId( $user['team']['id'] );
        foreach( $team_members as &$team_member )
        {
            $team_member['is_checkin'] = count( array_filter($checkins, function($item)use($team_member){ return $item['user_id'] == $team_member['user_id'];}) ) ? true : false;
        }


        $isLogin = false;
        if( isset( $_SESSION['id'] ) ) {
            // TODO 本来の意味的には逆、微妙なのでその内直す
            $isLogin = true;
        }
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->assign( "login", $isLogin );

        $smarty->assign( "match",            $match );
        $smarty->assign( "user",             $user );
        $smarty->assign( "checkins",         $checkins );
        $smarty->assign( "team_members",     $team_members );

        $smarty->display('Match/MatchCheckinForm.tmpl');
    }
    /**
     * 試合へのチェックインするやつ(commit)
     * 
     */
    public function checkin_commit()
    {
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();

        $db = new Db();

        // 試合情報とチェックイン情報を取ってきてsmartyに渡す。
        $match_id = $_REQUEST['match_id'];
        $match    = new Match( $db, $match_id );
        // 適当なmatch_idはNG。
        if( empty($match) )
        {
            self::displayCommonScreen( ERR_HEAD_COMMON, '試合がみつかりません。' );
            exit;
        }

        // このマッチのhost_team_id/apply_team_idどちらかの、team_ownerじゃないとNG。
        $user         = User::info( $_SESSION["id"] );
        $owner_team_ids = array_map( function($item){ return $item['team_id']; }, $user['team_owners'] );
        if( empty($user['team']) || (!in_array($match->host_team_id, $owner_team_ids) && !in_array($match->apply_team_id, $owner_team_ids)) )
        {
            self::displayCommonScreen( ERR_HEAD_COMMON, 'この試合のチームの代表者である必要があります。' );
            exit;
        }


        $checkins     = MatchCheckin::getByMatchIdTeamId( $match_id, $user['team']['id'] );
        $team_members = TeamMembers::getByTeamId( $user['team']['id'] );


        $checkin_user_ids = [];
        foreach( $team_members as $team_member )
        {
            // formからは "user_○○"(users.id)のフィールド名でチェックされたやつがPOSTされるので、キャッチする
            $checkbox_name = 'user_' . $team_member['user_id'];
            if( isset($_REQUEST[$checkbox_name]) && !empty($_REQUEST[$checkbox_name]) )
            {
                // チェックインするusers.idを集めておく。
                $checkin_user_ids[] = $team_member['user_id'];
            }
        }
        // 5名じゃなかったらNG。
        if( count($checkin_user_ids) != 5 )
        {
            self::displayCommonScreen( ERR_HEAD_COMMON, 'チェックインのメンバーを 5名 指定してください。' );
            exit;
        }

        $db = new Db();
        $db->beginTransaction();
        foreach( $checkin_user_ids as $uid )
        {
            // team_membersにいないuser_idでpostされてたらNG。
            $team_member = current( array_filter($team_members, function($item)use($uid){ return $item['user_id'] == $uid;}) );
            if( empty($team_member) )
            {
                self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
                exit;
            }

            // 修正の場合もあるので、チェックイン情報があるか取ってきてみる。
            $checkin = MatchCheckin::findByMatchIdTeamIdUserId($match_id, $user['team']['id'], $uid);
            if( empty($checkin) )
            {
                $match_checkin = new MatchCheckin($db);
            }
            else
            {
                $match_checkin = new MatchCheckin($db, $checkin['id']);
            }
            $match_checkin->match_id    = $match_id;
            $match_checkin->team_id     = $user['team']['id'];
            $match_checkin->user_id     = $uid;
            $match_checkin->summoner_id = $team_member['summoner_id'];
            $match_checkin->save();
        }
        $db->commit();

        // マッチ詳細画面へリダイレクト
        header('location: /Match/Display?match_id='.$match_id);
        exit();
    }



	public function displayMatchingForm(){
		$isLogin = false;
		if( isset( $_SESSION['id'] ) ) {
			// TODO 本来の意味的には逆、微妙なのでその内直す
			$isLogin = true;
		}
		$smarty = new Smarty();
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		$smarty->assign( "login", $isLogin );
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
