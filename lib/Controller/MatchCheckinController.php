<?php
require_once( PATH_CONTROLLER   . 'BaseController.php' );
require_once( PATH_MODEL . 'Match.php' );
require_once( PATH_MODEL . 'MatchCheckin.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'ManagementObservers.php' );

require_once( PATH_RIOTAPI . 'PublishTournamentCode.php' );


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
            self::displayCommonError([
                'message' => "試合がみつかりません。",
                'button'   => [
                    'href'      => "/Match/recruitList" ,
                    'name'      => "試合一覧へ戻る",
                ],
            ]);
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
                $checkins     = $match->getCheckinByTeamId( $iTeamId );
                
                $team_members = TeamMembers::getByTeamId( $iTeamId );
                $enable_checkin_members = [];
                foreach( $team_members as $team_member ){
                    if( $team_member["summoner_id"] != null ){
                        $enable_checkin_members[] = $team_member;
                    }
                }
                
                $asAuthorizedTeamInfo["team_members"] = $enable_checkin_members;
                
                $team_staffs = TeamStaffs::getByTeamId( $iTeamId );
                $enable_checkin_staffs = [];
                foreach( $team_staffs as $team_staff ){
                    if( $team_staff["summoner_id"] != null ){
                        $enable_checkin_staffs[] = $team_staff;
                    }
                }
                $asAuthorizedTeamInfo["team_staffs"] = $enable_checkin_staffs;
                
                $team_contacts = TeamContact::getByTeamId( $iTeamId );
                $enable_checkin_contacts = [];
                foreach( $team_contacts as $team_contact ){
                    if( $team_contact["summoner_id"] != null ){
                        $enable_checkin_contacts[] = $team_contact;
                    }
                }
                $asAuthorizedTeamInfo["team_contact"] = $enable_checkin_contacts;
                
                $ahsMatchTeam[] = $asAuthorizedTeamInfo;
            }
        }
        
        if( count( $ahsMatchTeam ) == 0 )
        {
            self::displayCommonError([
                'message' => "この試合のチームの代表者か連絡者である必要があります。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
            exit;
        }
        
        $test = ManagementObservers::getAllObservers();
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->assign( "login", true );

        $smarty->assign( "match"    , $match );
        $smarty->assign( "teams"    , $ahsMatchTeam );

        $smarty->display('MatchCheckin/form.tmpl');
    }
    
    /**
     * 試合へのチェックインするやつ(confirm)
     * 
     */
    public function confirm()
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
            self::displayCommonError([
                'message' => "試合がみつかりません。",
                'button'   => [
                    'href'      => "/Match/recruitList" ,
                    'name'      => "試合一覧へ戻る",
                ],
            ]);
            exit;
        }
        
        $iTeamId = null;
        if( isset( $_REQUEST["team_id"] ) ){
            $iTeamId    = $_REQUEST["team_id"];
            $oTeam      = new Teams( $db, $iTeamId );
        }else{
            self::displayCommonError([
                'message' => "チームを選択していません。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
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
            self::displayCommonError([
                'message' => "チームを選択していません。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
            exit;
        }

        $checkins     = $match->getCheckinByTeamId( $iTeamId );
        $team_members = TeamMembers::getByTeamId( $iTeamId );

        $checkin_members = [];
        foreach( $team_members as $team_member )
        {
            // formからは "user_○○"(users.id)のフィールド名でチェックされたやつがPOSTされるので、キャッチする
            $checkbox_name = 'member_' . $team_member['user_id'];
            if( isset($_REQUEST[$checkbox_name]) && !empty($_REQUEST[$checkbox_name]) )
            {
                // チェックインするusers.idを集めておく。
                $checkin_members[] = $team_member;
            }
        }
        
        // 5名じゃなかったらNG。
        if( count($checkin_members) != 5 )
        {
            self::displayCommonError([
                'message' => "チェックインのメンバーを 5名 指定してください。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
            exit;
        }
        
        // 観戦者の確認
        $checkin_observer = [];
        if( isset($_REQUEST["observer"]) && !empty($_REQUEST["observer"]) ){
            $isTeamMeber = $oTeam->getTeamMemberInfoById($_REQUEST["observer"]);
            if( !isset( $isTeamMeber ) || empty( $isTeamMeber ) ){
                self::displayCommonError([
                    'message' => "チームのメンバー以外が観戦者に選ばれています。",
                    'button'   => [
                        'href'      => "/Match/display?match_id=" . $match_id ,
                        'name'      => "試合詳細へ戻る",
                    ],
                ]);
                exit;
            }
            $oUser = new User( $db, $_REQUEST["observer"] );
            $checkin_observer[] = $oUser;
        }
        
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->assign( "login", true );

        $smarty->assign( "match"    , $match );
        $smarty->assign( "team"     , $oTeam );
        $smarty->assign( "checkin_members"    , $checkin_members );
        $smarty->assign( "checkin_observer"    , $checkin_observer );

        $smarty->display('MatchCheckin/confirm.tmpl');
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
            self::displayCommonError([
                'message' => "試合がみつかりません。",
                'button'   => [
                    'href'      => "/Match/recruitList" ,
                    'name'      => "試合一覧へ戻る",
                ],
            ]);
            exit;
        }
        
        $iTeamId = null;
        if( isset( $_REQUEST["team_id"] ) ){
            $iTeamId = $_REQUEST["team_id"];
            $oTeam      = new Teams( $db, $iTeamId );
        }else{
            self::displayCommonError([
                'message' => "チームを選択していません。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
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
            self::displayCommonError([
                'message' => "チェックインの権限がありません。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
            exit;
        }

        $checkins     = $match->getCheckinByTeamId( $iTeamId );
        $team_members = TeamMembers::getByTeamId( $iTeamId );

        $checkin_user_ids = [];
        foreach( $team_members as $team_member )
        {
            // formからは "user_○○"(users.id)のフィールド名でチェックされたやつがPOSTされるので、キャッチする
            $checkbox_name = 'member_' . $team_member['user_id'];
            if( isset($_REQUEST[$checkbox_name]) && !empty($_REQUEST[$checkbox_name]) )
            {
                // チェックインするusers.idを集めておく。
                $checkin_user_ids[] = $team_member['user_id'];
            }
        }
        // 5名じゃなかったらNG。
        if( count($checkin_user_ids) != 5 )
        {
            self::displayCommonError([
                'message' => "チェックインのメンバーを 5名 指定してください。",
                'button'   => [
                    'href'      => "/Match/display?match_id=" . $match_id ,
                    'name'      => "試合詳細へ戻る",
                ],
            ]);
            exit;
        }
        
        $checkin_observer_ids = [];
        if( isset($_REQUEST["observer"]) && !empty($_REQUEST["observer"]) ){
            $isTeamMeber = $oTeam->getTeamMemberInfoById($_REQUEST["observer"]);
           if( !isset( $isTeamMeber ) || empty( $isTeamMeber ) ){
                self::displayCommonError([
                    'message' => "チームのメンバー以外が観戦者に選ばれています。",
                    'button'   => [
                        'href'      => "/Match/display?match_id=" . $match_id ,
                        'name'      => "試合詳細へ戻る",
                    ],
                ]);
                exit;
            }
            $checkin_observer_ids[] = $_REQUEST["observer"];
        }
        
        $db->beginTransaction();
        // 選手
        foreach( $checkin_user_ids as $uid ){
            // team_membersにいないuser_idでpostされてたらNG。
            $team_member = current( array_filter($team_members, function($item)use($uid){ return $item['user_id'] == $uid;}) );
            if( empty($team_member) ){
                self::displayCommonError([
                    'message' => "チームのメンバー以外が選ばれています。",
                    'button'   => [
                        'href'      => "/Match/display?match_id=" . $match_id ,
                        'name'      => "試合詳細へ戻る",
                    ],
                ]);
                exit;
            }
            
            $match_checkin = new MatchCheckin($db);
            
            $match_checkin->match_id    = $match_id;
            $match_checkin->team_id     = $iTeamId;
            $match_checkin->user_id     = $uid;
            $match_checkin->type        = MatchCheckin::TYPE_MEMBER;
            $match_checkin->summoner_id = $team_member['summoner_id'];
            $match_checkin->save();
        }
        
        // 観戦者
        // TODO どっかのタイミングで関数化して↑と共通化、ついでに今はユニーク制約でエラーになるけど選手と観戦が被ってる場合のエラー処理
        foreach( $checkin_observer_ids as $uid ){
            $match_checkin = new MatchCheckin($db);
            
            $match_checkin->match_id    = $match_id;
            $match_checkin->team_id     = $iTeamId;
            $match_checkin->user_id     = $uid;
            $match_checkin->type        = MatchCheckin::TYPE_OBSERVER;
            $match_checkin->summoner_id = $team_member['summoner_id'];
            $match_checkin->save();
        }
        
        
        // どっちのチームもチェックインしたらトーナメントコード発行
        if( $match->getCheckinStatus( $db ) ){
            $asSummoners = [];
            
            $asCheckinMember = $match->getCheckinMemberSummonerId( $db );
            
            $api = new PublishTournamentCode();
            $api->setParams([
                    "allowedSummonerIds" => $asCheckinMember,
                    "mapType"       => "SUMMONERS_RIFT",
                    "metadata"      => "",
                    "pickType"      => "TOURNAMENT_DRAFT",
                    "spectatorType" => "ALL",
                    "teamSize"      => 5
                ]
            );
            
            $json = $api->execApi();
            
            if($json){
                $match->tournament_code = $json[0];
                $match->save();
            }
        }
        
        $db->commit();

        // マッチ詳細画面へリダイレクト
        header('location: /Match/Display?match_id='.$match_id);
        exit();
    }
    
    /**
     * // [SubFunction]汎用エラー画面だすやつ
     *
     * @param  array                 $param       // []
     */
    public function displayCommonError( $param = [] )
    {
        $param_org = [
            'title'   => "エラーが発生しました。",
            'message' => "もう一度やり直してください。",
            'button'   => [
                'href'      => "/index.html",
                'name'      => "戻る",
            ],
        ];
        $error = array_merge( $param_org, $param );
        $smarty = new Smarty();
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        $smarty->default_modifiers[] = 'escape:html';
        
        $smarty->assign("error", $error);
        $smarty->display('commonError.tmpl');
    }

}