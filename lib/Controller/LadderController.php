<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'Ladder.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'League.php' );

class LadderController extends BaseController{

	public function __construct(){
	}
	
	public function display(){
		session_set_save_handler( new MysqlSessionHandler() );
		@session_start();
		$isLogin = false;
		if( isset( $_SESSION['id'] ) ) {
			// TODO –{—ˆ‚ÌˆÓ–¡“I‚É‚Í‹tA”÷–­‚È‚Ì‚Å‚»‚Ì“à’¼‚·
			$isLogin = true;
		}
		
		$oDb = new Db();
		
		$oLadder = Ladder::getLadderRanking( $oDb );
		
		$ahsLadder = [];
		$iOrder			= 1;
		$iBeforeRank	= -1;
		$iBeforePoint	= -1;
		while( $row = $oLadder->fetch_assoc() ) {
			if( $iBeforeRank >= 0 ){
				if( ( $iBeforeRank != $row["rank"] ) || ( $iBeforePoint != $row["point"] ) ){
					$iOrder++;
				}
			}
			
			$oTeam = new Teams( $oDb, $row["team_id"] );
			
			$ahsTeamLadder = [];
			$ahsTeamLadder["order"]			= $iOrder;
			$ahsTeamLadder["team_id"]		= $oTeam->team_id;
			$ahsTeamLadder["team_name"]		= $oTeam->team_name;
			$ahsTeamLadder["league_id"]		= $row["league_id"];
			$ahsTeamLadder["league_name"]	= $row["league_name"];
			$ahsTeamLadder["rank"]			= $row["rank"];
			$ahsTeamLadder["point"]			= $row["point"];
			
			$ahsLadder[] = $ahsTeamLadder;
			$iBeforeRank	= $row["rank"];
			$iBeforePoint	= $row["point"];
		}
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "ladder_list", $ahsLadder );
		$smarty->assign( "login", $isLogin );
		
		$smarty->display('LadderRanking.tmpl');
	}
}