<?php
require_once( PATH_CONTROLLER . 'BaseController.php' );
require_once( PATH_MODEL . 'LadderRanking.php' );
require_once( PATH_MODEL . 'Team.php' );
require_once( PATH_MODEL . 'League.php' );

class LadderRankingController extends BaseController{

	public function __construct(){
	}
	
	public function display(){
		session_set_save_handler( new MysqlSessionHandler() );
		@session_start();
		$isLogin = false;
		if( !isset( $_SESSION['id'] ) ) {
			// TODO –{—ˆ‚ÌˆÓ–¡“I‚É‚Í‹tA”÷–­‚È‚Ì‚Å‚»‚Ì“à’¼‚·
			$isLogin = true;
		}
		
		$oDb = new Db();
		
		$oLadderRanking = LadderRanking::getLadderRanking( $oDb );
		
		$ahsLadderRanking = [];
		$iOrder			= 1;
		$iBeforeRank	= -1;
		$iBeforePoint	= -1;
		while( $row = $oLadderRanking->fetch_assoc() ) {
			if( $iBeforeRank >= 0 ){
				if( ( $iBeforeRank != $row["rank"] ) && ( $iBeforePoint != $row["point"] ) ){
					$iOrder++;
				}
			}
			
			$oTeam = new Team( $oDb, $row["team_id"] );
			
			$ahsTeamLadder = [];
			$ahsTeamLadder["order"]			= $iOrder;
			$ahsTeamLadder["team_id"]		= $oTeam->team_id;
			$ahsTeamLadder["team_name"]		= $oTeam->team_name;
			$ahsTeamLadder["league_id"]		= $row["league_id"];
			$ahsTeamLadder["league_name"]	= $row["league_name"];
			$ahsTeamLadder["rank"]			= $row["rank"];
			$ahsTeamLadder["point"]			= $row["point"];
			
			$ahsLadderRanking[] = $ahsTeamLadder;
			$iBeforeRank	= $row["rank"];
			$iBeforPoint	= $row["point"];
		}
		
		$smarty = new Smarty();
		
		$smarty->template_dir = PATH_TMPL;
		$smarty->compile_dir  = PATH_TMPL_C;
		
		$smarty->assign( "ladder_list", $ahsLadderRanking );
		$smarty->assign( "login", $isLogin );
		
		$smarty->display('LadderRanking.tmpl');
	}
}