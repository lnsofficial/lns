<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "League.php" );
require_once( PATH_MODEL . "LastJoin.php" );
require_once( PATH_MODEL . "LadderRanking.php" );
require_once( PATH_MODEL . "Member.php" );

class Team extends Base{
	const MAIN_TABLE			= "m_team";
	const COL_ID				= "team_id";
	
	// カラム
	const DATA	= [
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"mail_address"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"skype_id"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_name"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_name_kana"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_tag"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"team_tag_kana"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"comment"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
	];
	
	public function getLastJoin( $oDb ){
		$sSelectLastJoin = "SELECT last_join_id FROM t_last_join WHERE team_id = ? AND state = ? ORDER BY join_date DESC";
		$ahsParameter = [ $this->team_id, LastJoin::STATE_ENABLE ];
		
		$oResult = $this->db->executePrepare( $sSelectLastJoin, "ii", $ahsParameter );
		
		$oLastJoin = null;
		while( $row = $oResult->fetch_array() ){
			$iLastJoinId = $row["last_join_id"];
			$oLastJoin = new LastJoin( $oDb, $iLastJoinId );
			break;
		}
		
		return $oLastJoin;
	}
	
	public function getBeforeLadder( $oDb ){
		$sSelectTermSql = "SELECT MAX(term) as term FROM t_ladder_ranking";
		$oTerm = $oDb->execute( $sSelectTermSql );
		
		$iTerm = 0;
		while( $row = $oTerm->fetch_assoc() ) {
			$iTerm = $row["term"];
		}
		
		if( $iTerm == 0 ){
			return false;
		}elseif( $iTerm > 1 ){
			$iTerm--;
		}
		
		$sSelectLadder = "SELECT * FROM t_ladder_ranking WHERE team_id = ? AND term = ?";
		$ahsParameter = [ $this->team_id, $iTerm ];
		
		$oResult = $oDb->executePrepare( $sSelectLadder, "ii", $ahsParameter );
		
		$oLadder = null;
		while( $row = $oResult->fetch_array() ){
			$oLadder = new LadderRanking( $oDb, $row["ladder_id"] );
			break;
		}
		
		return $oLadder;
	}
	
	public function getCurrentLadder( $oDb ){
		$sSelectLadder = "SELECT * FROM t_ladder_ranking WHERE team_id = ? ORDER BY term DESC";
		$ahsParameter = [ $this->team_id ];
		$oResult = $oDb->executePrepare( $sSelectLadder, "i", $ahsParameter );
		
		$oLadder = null;
		while( $row = $oResult->fetch_array() ){
			$oLadder = new LadderRanking( $oDb, $row["ladder_id"] );
			break;
		}
		
		return $oLadder;
	}
	
	public function getLeague( $oDb ){
		$oLadder = $this->getCurrentLadder( $oDb );
		$oLeague = new League( $oDb, $oLadder->league_id );
		
		return $oLeague;
	}
	
	public function getTeamMember( $oDb ){
		$sSelectTeamMember = "SELECT * FROM m_member WHERE team_id = ?";
		$ahsParameter = [ $this->team_id ];
		
		$oResult = $oDb->executePrepare( $sSelectTeamMember, "i", $ahsParameter );
		
		$aoTeamMember = [];
		while( $row = $oResult->fetch_assoc() ) {
			$aMember = [];
			$aMember["id"]				= $row["member_id"];
			$aMember["summoner_name"]	= $row["summoner_name"];
			
			$aoTeamMember[] = $aMember;
		}
		
		return $aoTeamMember;
	}
}
