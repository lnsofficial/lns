<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Match.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'User.php' );

class MatchResultController extends BaseController{
    const DISPLAY_DIR_PATH	= "Match";
    const DISPLAY_FILE_PATH	= "Match_common";

    public function __construct(){
    }

    public function form(){
        // TODO この辺共通処理に移動
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        
        $iMatchId = intval( $_REQUEST["match_id"] );
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $iCurTeamId = $_REQUEST["team_id"];
        
        $oApplyTeam = new Teams( $oDb, $iCurTeamId );
        $authorized = $oApplyTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        // マッチ情報取得
        $oMatch = new Match( $oDb, $iMatchId );
        
        $oHostTeam = new Teams( $oDb, $oMatch->host_team_id );
        $oApplyTeam = new Teams( $oDb, $oMatch->apply_team_id );
        
        // 試合のホスト・ゲスト以外はエラー
        if( ( $iCurTeamId != $oMatch->host_team_id && $iCurTeamId != $oMatch->apply_team_id ) ){
            self::displayCommonScreen( ERR_HEAD_COMMON , ERR_MATCH_PERMISSION );
            exit;
        }
        
        // 試合結果の登録可能時間を過ぎての登録はエラー
        if( !$oMatch->expirationRegistMatchResult() ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_OVER_RESULT_REGIST );
            exit;
        }
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign( "match_id", $iMatchId );
        $smarty->assign( "team_id", $iCurTeamId );
        $smarty->assign( "host_team_name", $oHostTeam->team_name );
        $smarty->assign( "host_team_id", $oHostTeam->id );
        $smarty->assign( "apply_team_name", $oApplyTeam->team_name );
        $smarty->assign( "apply_team_id", $oApplyTeam->id );
        
        $smarty->display( 'Match/MatchResult_form.tmpl' );
    }
    
    public function confirm(){
        // TODO この辺共通処理に移動
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        
        $iMatchId = intval( $_REQUEST["match_id"] );
        $iWinnerTeamId = intval( $_REQUEST["winner_team"] );
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $iCurTeamId = $_REQUEST["team_id"];
        
        $oApplyTeam = new Teams( $oDb, $iCurTeamId );
        $authorized = $oApplyTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        // マッチ情報取得
        $oMatch = new Match( $oDb, $iMatchId );
        
        // 試合のホスト・ゲスト以外がキャンセルしようとしたらエラー
        if( ( $iCurTeamId != $oMatch->host_team_id && $iCurTeamId != $oMatch->apply_team_id ) ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        // 試合結果の登録可能時間を過ぎての登録はエラー
        if( !$oMatch->expirationRegistMatchResult() ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_OVER_RESULT_REGIST );
            exit;
        }
        
        $oHostTeam = new Teams( $oDb, $oMatch->host_team_id );
        $oApplyTeam = new Teams( $oDb, $oMatch->apply_team_id );
        $oWinnerTeam = null;
        switch( $iWinnerTeamId ){
            case $oHostTeam->id:
                $oWinnerTeam = $oHostTeam;
                break;
            case $oApplyTeam->id:
                $oWinnerTeam = $oApplyTeam;
                break;
            default:
                // 勝者が参加者のどっちでもなければエラー
                self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
                exit;
        }
        
        // 仮アップ
        // TODO 仮アップして確認で終わったやつどう消そう
        if( $_FILES["result_image"] ){
            $sMimeType = $_FILES["result_image"]["type"];
            $asType = explode( "/", $sMimeType );
            $sFileType = $asType[0];
            if( $sFileType != "image" ){
                self::displayCommonScreen( ERR_HEAD_COMMON, ERR_COMMON_INPUT );
                exit;
            }
            $sExtension = "." . $asType[1];
            move_uploaded_file($_FILES["result_image"]["tmp_name"], PATH_TMP_IMAGE . $iMatchId . $sExtension );
        }
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign( "match_id", $iMatchId );
        $smarty->assign( "team_id", $iCurTeamId );
        $smarty->assign( "winner_team_id", $iWinnerTeamId );
        $smarty->assign( "winner_team_name", $oWinnerTeam->team_name );
        $smarty->assign( "image_extension", $sExtension );
        
        $smarty->display( "Match/MatchResult_confirm.tmpl" );
    }
    
    public function register(){
        // TODO この辺共通処理に移動
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        $oDb = new Db();
        
        $iMatchId = intval( $_REQUEST["match_id"] );
        $iWinnerTeamId = intval( $_REQUEST["winner_team"] );
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        
        $iCurTeamId = $_REQUEST["team_id"];
        
        $oApplyTeam = new Teams( $oDb, $iCurTeamId );
        $authorized = $oApplyTeam->isAuthorized( $oLoginUser->id );
        if( !$authorized ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        $sResultImageName = $_REQUEST["result_image_name"];
        
        // マッチ情報取得
        $oMatch = new Match( $oDb, $iMatchId );
        
        // 現在のチームが参加者のどっちでもなければエラー
        if( ( $iCurTeamId != $oMatch->host_team_id && $iCurTeamId != $oMatch->apply_team_id ) ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_PERMISSION );
            exit;
        }
        
        // 試合結果の登録可能時間を過ぎての登録はエラー
        if( !$oMatch->expirationRegistMatchResult() ){
            self::displayCommonScreen( ERR_HEAD_COMMON, ERR_MATCH_OVER_RESULT_REGIST );
            exit;
        }
        
        $oWinnerTeam = null;
        switch( $iWinnerTeamId ){
            case $oMatch->host_team_id:
                break;
            case $oMatch->apply_team_id:
                break;
            default:
                // 勝者が参加者のどっちでもなければエラー
                self::displayCommonScreen( ERR_HEAD_COMMON , ERR_COMMON_INPUT );
                exit;
        }
        
        // ファイル本アップ
        rename( PATH_TMP_IMAGE . $sResultImageName, PATH_IMAGE . $sResultImageName );
        
        // 更新
        $oDb->beginTransaction();
        
        $oMatch->screen_shot_url = $sResultImageName;
        $oMatch->state = Match::MATCH_STATE_FINISHED;
        $oMatch->winner = $iWinnerTeamId;
        $oMatch->save();
        
        $oDb->commit();
        
        self::displayCommonScreen( MSG_HEAD_MATCH_COMPLETE, MSG_MATCH_RESULT_COMPLETE );
    }
    
    public function detail( $match_id ){
        session_set_save_handler( new MysqlSessionHandler() );
        @session_start();
        
        $db = new Db();
        $login = false;
        if( isset( $_SESSION["id"] ) ){
            $login = true;
         }
        
        $match = new Match( $db, $match_id );
        
        $match_info = json_decode( $match->match_info );
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign( "match"        , $match );
        $smarty->assign( "match_info"   , $match_info );
        $smarty->assign( "login"        , $login );
        
        $smarty->display( "MatchResult/detail.tmpl" );
    }

}
    