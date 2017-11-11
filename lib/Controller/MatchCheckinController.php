<?php
require_once( PATH_CONTROLLER   . 'BaseController.php' );
require_once( PATH_MODEL . 'Match.php' );
require_once( PATH_MODEL . 'MatchCheckin.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_MODEL . 'Teams.php' );


class MatchCheckinController extends BaseController{
	
    public function __construct(){
    }

    /**
     * 試合へのチェックインするやつ(form)
     * 
     */
    public function form()
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
        $oLoginUser     = new User( $db, $_SESSION["id"] );
        
        // 権限あるチームの情報取得
        $ahsAuthorizedTeamInfo = $oLoginUser->getAuthorizedTeam();
        
        // 権限あるチームの内、この試合に出てるチームから選択
        $ahsMatchTeam = [];
        foreach( $ahsAuthorizedTeamInfo as $asAuthorizedTeamInfo ){
            $iTeamId = $asAuthorizedTeamInfo["id"];
            if( $iTeamId == $match->host_team_id || $iTeamId == $match->apply_team_id ){
                // team_membersにcheckinsの情報付け足す。
                $checkins     = MatchCheckin::getByMatchIdTeamId( $match_id, $iTeamId );
                
                $team_members = TeamMembers::getByTeamId( $iTeamId );
                foreach( $team_members as &$team_member )
                {
                    $team_member['is_checkin'] = count( array_filter($checkins, function($item)use($team_member){ return $item['user_id'] == $team_member['user_id'];}) ) ? true : false;
                }
                $asAuthorizedTeamInfo["team_members"] = $team_members;
                $ahsMatchTeam[] = $asAuthorizedTeamInfo;
            }
        }
        
        if( count( $ahsMatchTeam ) == 0 )
        {
            self::displayCommonScreen( ERR_HEAD_COMMON, 'この試合のチームの代表者か連絡者である必要があります。' );
            exit;
        }
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->assign( "login", true );

        $smarty->assign( "match"    , $match );
        $smarty->assign( "teams"    , $ahsMatchTeam );

        $smarty->display('MatchCheckin/form.tmpl');
    }
    /**
     * 試合へのチェックインするやつ(commit)
     * 
     */
    public function commit()
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
        
        $iTeamId = null;
        if( isset( $_REQUEST["team_id"] ) ){
            $iTeamId = $_REQUEST["team_id"];
        }else{
            self::displayCommonScreen( ERR_HEAD_COMMON, 'チームを選択していません。' );
            exit;
        }
        
        // このマッチのhost_team_id/apply_team_idどちらかの、team_ownerじゃないとNG。
        $oLoginUser     = new User( $db, $_SESSION["id"] );
        
        // 権限あるチームの情報取得
        $ahsAuthorizedTeamInfo = $oLoginUser->getAuthorizedTeam();
        
        // 権限あるチームがこの試合に出てるチームか
        $bMatchTeam = false;
        foreach( $ahsAuthorizedTeamInfo as $asAuthorizedTeamInfo ){
            if( $iTeamId != $asAuthorizedTeamInfo["id"] ){
                continue;
            }
            if( $iTeamId == $match->host_team_id || $iTeamId == $match->apply_team_id ){
                $bMatchTeam = true;
                break;
            }
        }
        if( !$bMatchTeam ){
            self::displayCommonScreen( ERR_HEAD_COMMON, 'チェックインの権限がありません。' );
            exit;
        }

        $checkins     = MatchCheckin::getByMatchIdTeamId( $match_id, $iTeamId );
        $team_members = TeamMembers::getByTeamId( $iTeamId );

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
        
        $db->beginTransaction();
        if( count( $checkins ) > 0 ){
            // すでにチェックイン済み
            $checkin_num = 0;
            foreach( $checkins as $checkin ){
                $iUserId = $checkin_user_ids[$checkin_num];
                $match_checkin = new MatchCheckin( $db, $checkin["id"] );
                
                $oUser = new User( $db, $iUserId );
                
                $match_checkin->user_id     = $oUser->id;
                $match_checkin->summoner_id = $oUser->summoner_id;
                $match_checkin->save();
                $checkin_num++;
            }
        }else{
            // 未チェックイン
            foreach( $checkin_user_ids as $uid ){
                // team_membersにいないuser_idでpostされてたらNG。
                $team_member = current( array_filter($team_members, function($item)use($uid){ return $item['user_id'] == $uid;}) );
                if( empty($team_member) ){
                    self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
                    exit;
                }
                
                $match_checkin = new MatchCheckin($db);
                
                $match_checkin->match_id    = $match_id;
                $match_checkin->team_id     = $iTeamId;
                $match_checkin->user_id     = $uid;
                $match_checkin->summoner_id = $team_member['summoner_id'];
                $match_checkin->save();
            }
        }
        
        $db->commit();

        // マッチ詳細画面へリダイレクト
        header('location: /Match/Display?match_id='.$match_id);
        exit();
    }

}