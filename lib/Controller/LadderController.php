<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Ladder.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'User.php' );
require_once( PATH_MODEL . 'League.php' );
require_once( PATH_MODEL . 'TeamOwner.php' );
require_once( PATH_MODEL . 'UserRank.php' );

class LadderController extends BaseController{

    public function __construct(){
    }
    
    public function display(){
        session_set_save_handler( new MysqlSessionHandler() );
        @session_start();
        $isLogin = false;
        if( isset( $_SESSION['id'] ) ) {
            // TODO 本来の意味的には逆、微妙なのでその内直す
            $isLogin = true;
        }
        
        $oDb = new Db();
        
        $oLadder = Ladder::getLadderRanking( $oDb );
        
        $ahsLadder = [];
        $iOrder            = 1;
        $iBeforeRank    = -1;
        $iBeforePoint    = -1;
        if( !empty( $oLadder ) ){
            while( $row = $oLadder->fetch_assoc() ) {
                if( $iBeforeRank >= 0 ){
                    if( ( $iBeforeRank != $row["rank"] ) || ( $iBeforePoint != $row["point"] ) ){
                        $iOrder++;
                    }
                }
                
                $oTeam = new Teams( $oDb, $row["team_id"] );
                
                $ahsTeamLadder = [];
                $ahsTeamLadder["order"]            = $iOrder;
                $ahsTeamLadder["team_id"]        = $oTeam->id;
                $ahsTeamLadder["team_name"]        = $oTeam->team_name;
                $ahsTeamLadder["league_id"]        = $row["league_id"];
                $ahsTeamLadder["league_name"]    = $row["league_name"];
                $ahsTeamLadder["rank"]            = $row["rank"];
                $ahsTeamLadder["point"]            = $row["point"];
                
                $ahsLadder[] = $ahsTeamLadder;
                $iBeforeRank    = $row["rank"];
                $iBeforePoint    = $row["point"];
            }
        }
        
        $smarty = new Smarty();
        
        $smarty->template_dir = PATH_TMPL;
        $smarty->compile_dir  = PATH_TMPL_C;
        
        $smarty->assign( "ladder_list", $ahsLadder );
        $smarty->assign( "login", $isLogin );
        
        $smarty->display('LadderRanking.tmpl');
    }
    
    public function join(){
        session_set_save_handler( new MysqlSessionHandler() );
        require_logined_session();
        
        if( !$_REQUEST["team_id"] ){
            self::displayError();
            exit;
        }else{
            $iTeamId = $_REQUEST["team_id"];
        }
        
        $oDb = new Db();
        
        $oTeam = new Teams( $oDb, $iTeamId );
        $oLoginUser = new User( $oDb, $_SESSION["id"] );
        $ahsTeamMembers = TeamMembers::getByTeamId( $oTeam->id );
        $oTeamOwner   = TeamOwner::getUserIdFromTeamId( $oTeam->id );
        
        if( $oLoginUser->id !== $oTeamOwner->id ){
            self::displayError();
            exit;
        }
        
        if( count($ahsTeamMembers) < Teams::COUNT_MIN_JOIN_LADDER ){
            self::displayError();
            exit;
        }
        
        // 各メンバーのtier/rankからチーム力を算出する
        $oUserRank = new UserRank($oDb);
        $iCalcCount = 0;
        $iTotalTeamPower = 0;
        foreach( $ahsTeamMembers as $asMember )
        {
            $user_rank = $oUserRank->findHigherByUserId( $asMember['user_id'] );
            if( $user_rank['tier'] == 'UNRANK' )
            {
                continue;
            }
            $iCalcCount++;
            $iTotalTeamPower += User::RANK_LIST[$user_rank['tier']][$user_rank['rank']];
        }
        
        $iTeamPower = $iTotalTeamPower / $iCalcCount;
        // 適切なリーグを取ってくる
        $oLeague = League::getAssignLeague( $oDb, $iTeamPower );


        $oDb->beginTransaction();
        
        $oLadder = new Ladder( $oDb );
        $oLadder->team_id   = $oTeam->id;
        $oLadder->league_id = $oLeague->id;
        $oLadder->season    = SEASON_NOW;
        $oLadder->term      = Ladder::getCurrentTerm( $oDb );
        $oLadder->point     = 0;
        $oLadder->save();
        
        $oDb->commit();
        
        header('Location: /Ladder/display');
    }

	public function displayError(){
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->display('commonError.tmpl');
	}
}
