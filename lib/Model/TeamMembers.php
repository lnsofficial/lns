<?php
require_once( PATH_MODEL . "Base.php" );

class TeamMembers extends Base{
	const MAIN_TABLE	= "team_members";
	const COL_ID		= "id";
	
	// カラム
	const DATA	= [
		"id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"	    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"	    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];
	
	// TODO 特定カラムによる検索を共通化
	function getUsersFromTeamId( $team_id ){
		$oDb = new Db();
		
		$sSelectUser = "SELECT * FROM team_members WHERE team_id = ?";
		$ahsParameter = [ $team_id ];
		
		$oResult = $oDb->executePrepare( $sSelectUser, "s", $ahsParameter );
		
		$oUsers = null;
		while( $row = $oResult->fetch_array() ){
			$oUsers = new TeamMembers( $row["id"] );
			break;
		}
		
		return $oUsers;
	}

	function getTeamIdFromUserId( $user_id ){
		$oDb = new Db();
		
		$sSelectUser = "SELECT * FROM team_members WHERE user_id = ?";
		$ahsParameter = [ $user_id ];
		
		$oResult = $oDb->executePrepare( $sSelectUser, "s", $ahsParameter );
		
		$oTeam = null;
		while( $row = $oResult->fetch_array() ){
			$oTeam = new TeamMembers( $row["id"] );
			break;
		}
		
		return $oTeam;
	}
}
