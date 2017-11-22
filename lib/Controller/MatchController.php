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
    const DISPLAY_DIR_PATH    = "Match";
    const DISPLAY_FILE_PATH    = "Match_common";

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
        
        $oApplyTeam = null;
        if( $oMatch->apply_team_id ){
            $oApplyTeam = new Teams( $oDb, $oMatch->apply_team_id );
        }
        
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $ahsAuthorizedTeamInfo = $oLoginUser->getAuthorizedTeam();
        
        $ahsTeamInfo = [];
        
        foreach( $ahsAuthorizedTeamInfo as $asAuthorizedTeamInfo ){
            $oTeam = new Teams( $oDb, $asAuthorizedTeamInfo["id"] );
            $oTeamLadder = $oTeam->getCurrentLadder( $oDb );
            
            $asTeamInfo = [];
            $asTeamInfo["id"]       = $oTeam->id;
            $asTeamInfo["name"]     = $oTeam->team_name;
            $asTeamInfo["cancel"]   = false;
            $asTeamInfo["join"]     = false;
            $asTeamInfo["checkin"]  = false;
            $asTeamInfo["result"]   = false;
            
            if( !$oTeamLadder ){
                continue;
            }else{
                $oTeamLeague = new League( $oDb, $oTeamLadder->league_id );
            }
            
            switch( $oMatch->state ){
                case Match::MATCH_STATE_RECRUIT:
                    // 募集中
                    if(  $oTeam->id == $iHostTeamId ){
                        $asTeamInfo["cancel"] = true;
                    } else {
                        $asTeamInfo["join"]   = $oMatch->enableJoin( $oHostTeamLeague->rank, $oTeamLeague->rank );
                    }
                    break;
                case Match::MATCH_STATE_MATCHED:
                    if( $oTeam->id == $iHostTeamId || $oTeam->id == $oMatch->apply_team_id ){
                        if( $oMatch->enableCancel() ){
                            $asTeamInfo["cancel"] = true;
                        }
                        $asTeamInfo["checkin"]  = $oMatch->enableCheckin();
                        $asTeamInfo["result"]   = $oMatch->expirationRegistMatchResult();
                    }
                    break;
            }
            
            $ahsTeamInfo[] = $asTeamInfo;
        }
        
        $ahsHostCheckins  = MatchCheckin::getByMatchIdTeamId( $iMatchId, $oHostTeam->id );
        $ahsApplyCheckins = null;
        if( $oApplyTeam ){
            $ahsApplyCheckins = MatchCheckin::getByMatchIdTeamId( $iMatchId, $oApplyTeam->id );
        }

        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign( "match_info"           , $oMatch );
        $smarty->assign( "teams"                , $ahsTeamInfo );
        $smarty->assign( "host_team_name"       , $oHostTeam->team_name );
        if( $oApplyTeam ){
            $smarty->assign( "apply_team_name"  , $oApplyTeam->team_name );
        }
        if( isset( $ahsHostCheckins ) ){
            $smarty->assign( "host_checkin"     , $ahsHostCheckins );
        }
        if( isset( $ahsApplyCheckins ) ){
            $smarty->assign( "apply_checkin"    , $ahsApplyCheckins );
        }
        
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
        
        $oApplyTeam = new Teams( $oDb, $_REQUEST["team_id"] );
        $authorized = $oApplyTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        $oHostTeam  = new Teams( $oDb, $oMatch->host_team_id );
        // TODO エラー処理
        // TODO その内チームに所属リーグの情報引っ張ってくる関数作成
        $oHostTeamLadder = $oHostTeam->getCurrentLadder( $oDb );
        $oApplyTeamLadder = $oApplyTeam->getCurrentLadder( $oDb );
        
        // 申請側チームが「大会に参加」を押していないと、laddersに登録されていない。この場合はだめ！
        if( empty($oApplyTeamLadder) ){
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
                $dtLastJoin = date($oLatestLastJoin->joined_at);
                if( date('Y-m-d H:i:s') < date('Y-m-d H:i:s', strtotime($oLatestLastJoin->joined_at . " + 5 day") ) ){
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
        
        $oApplyTeam = new Teams( $oDb, $_REQUEST["team_id"] );
        $authorized = $oApplyTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        $iApplyTeamId = $oApplyTeam->id;
        
        // マッチ情報取得
        $oMatch = new Match( $oDb, $iMatchId );
        
        $oDb->beginTransaction();
        $oApplyTeamId = $oMatch->apply_team_id;
        
        switch( $iApplyTeamId ){
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
            $oApplyTeam = new Teams( $oDb, $oApplyTeamId );
            $oLastJoin = $oApplyTeam->getLastJoin( $oDb );
            $oLastJoin->state = TeamJoin::STATE_CANCEL;
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
            $ahsMatch["id"]                 = $row["id"];
            $ahsMatch["new"]                = $bNew;
            $ahsMatch["state"]              = $row["state"];
            $ahsMatch["match_date"]         = date('n月j日 H:i', strtotime( $row["match_date"]) );
            $ahsMatch["host_team_id"]       = $row["host_team_id"];
            $ahsMatch["host_team_name"]     = $oHostTeam->team_name;
            $ahsMatch["host_league_name"]   = $oHostLeague->league_name;
            if( $oApplyTeam ){
                $ahsMatch["apply_team_id"]      = $row["apply_team_id"];
                $ahsMatch["apply_team_name"]    = $oApplyTeam->team_name;
                $ahsMatch["apply_league_name"]  = $oApplyLeague->league_name;
            }
            $ahsMatch["winner"]             = $row["winner"];
            
            $ahsMatchList[] = $ahsMatch;
        }
        
        $oUser = new User( $oDb, $_SESSION["id"] );
        
        $ahsAuthorizedTeamInfo = $oUser->getAuthorizedTeam();
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign( "match_recruit_list"   , $ahsMatchList );
        $smarty->assign( "teams"                , $ahsAuthorizedTeamInfo );
        
        if( isset( $iState ) ){
            $smarty->assign( "state"            , $iState );
        }
        if( isset( $sStartDate ) ){
            $smarty->assign( "start_date"       , $sStartDate );
        }
        if( isset( $sEndDate ) ){
            $smarty->assign( "end_date"         , $sEndDate );
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
        if (!Match::checkRecruitMatchDate($_REQUEST["match_date"])) {
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_DISABLE_RECRUIT_TIME );
            exit;
        }

        $oDb = new Db();
        $oUser = new User( $oDb, $_SESSION["id"] );
        
        $iTeamId = $_REQUEST["team_id"];
        
        $this->checkRecruitEnable( $_REQUEST["match_date"], $iTeamId );

        // DBに登録
        $oDb = new Db();
        $oDb->beginTransaction();
        
        $oMatch = new Match( $oDb );
        $oMatch->host_team_id       = $iTeamId;
        $oMatch->match_date         = $_REQUEST["match_date"];
        $oMatch->recruit_start_date = date( 'Y-m-d H:i:s' );
        $oMatch->deadline_date      = $_REQUEST["deadline_date"];
        $oMatch->type               = $_REQUEST["type"];
        $oMatch->stream             = $_REQUEST["stream"];
        $oMatch->state              = Match::MATCH_STATE_RECRUIT;
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
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $oTeam = new Teams( $oDb, $_REQUEST["team_id"] );
        
        if( !$oTeam ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
            exit;
        }
        
        $ahsAuthorizedTeamInfo = $oLoginUser->getAuthorizedTeam();
        $bAuthorized = false;
        
        foreach( $ahsAuthorizedTeamInfo as $asTeamInfo ){
            if( $oTeam->id == $asTeamInfo["id"] ){
                $bAuthorized = true;
                break;
            }
        }
        if( !$bAuthorized ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
            exit;
        }
        
        $this->checkRecruitEnable( $_REQUEST["match_date"], $oTeam->id );
        
        // check time
        if (!Match::checkRecruitMatchDate($_REQUEST["match_date"])) {
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_DISABLE_RECRUIT_TIME );
            exit;
        }
        
        $dtMatchDate    = date( 'Y-m-d H:i:s', strtotime( $_REQUEST["match_date"] ) );
        $dtDeadlineDate = date( 'Y-m-d H:i:s', strtotime( $_REQUEST["deadline_date"] ) );

        self::displayMatchingConfirm($_REQUEST["type"], $dtMatchDate, $_REQUEST["stream"], $oTeam, $dtDeadlineDate);
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
        $bResult    = true;
        if( empty( $_REQUEST["type"] ) ){
            $bResult = false;
        }
        if( empty( $_REQUEST["match_date"] ) ){
            $bResult = false;
        } else {
            // 試合日時が現在日時より後の場合はエラー
            if( date( 'Y-m-d H:i:s' ) > date( 'Y-m-d H:i:s', strtotime( $_REQUEST["match_date"] ) ) ){
                $bResult = false;
            }
        }
        if( empty( $_REQUEST["deadline_date"] ) ){
            $bResult = false;
        } else {
            // 応募受付期限が現在日時より前、または試合日時より後の場合はエラー
            if( ( date( 'Y-m-d H:i:s' ) > date( 'Y-m-d H:i:s', strtotime( $_REQUEST["deadline_date"] ) ) ) || 
                ( date( 'Y-m-d H:i:s', strtotime( $_REQUEST["deadline_date"] ) ) > date( 'Y-m-d H:i:s', strtotime( $_REQUEST["match_date"] ) ) ) ){
                $bResult = false;
            }
        }
        if(!isset($_REQUEST["stream"])){
            $bResult = false;
        }
        if(!isset($_REQUEST["team_id"])){
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
        $isLogin = false;
        if( isset( $_SESSION['id'] ) ) {
            // TODO 本来の意味的には逆、微妙なのでその内直す
            $isLogin = true;
        }
        
        $oDb = new Db();
        
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        $ahsAuthorizedTeamInfo = $oLoginUser->getAuthorizedTeam();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->assign( "teams", $ahsAuthorizedTeamInfo );
        $smarty->assign( "login", $isLogin );
        $smarty->display('Match/MatchingForm.tmpl');
    }
    
    public function displayMatchingConfirm($type, $match_date, $stream, $oTeam, $deadline_date){
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;

        $smarty->assign("team",             $oTeam);
        $smarty->assign("type",             $type);
        $smarty->assign("match_date",       $match_date);
        $smarty->assign("deadline_date",    $deadline_date);
        $smarty->assign("stream",           $stream);

        $smarty->display('Match/MatchingForm_confirm.tmpl');
    }
}
