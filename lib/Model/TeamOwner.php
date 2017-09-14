<?php
require_once( PATH_MODEL . "Base.php" );

class TeamOwner extends Base{
	const MAIN_TABLE			= "team_owner";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"			    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];

    function getUserIdFromTeamId( $team_id ){
        $oDb = new Db();

        $sSelectUser = "SELECT * FROM team_owner WHERE team_id = ?";
        $ahsParameter = [ $team_id ];
        
        $oResult = $oDb->executePrepare( $sSelectUser, "s", $ahsParameter );
        
        $oTeam = $oResult->fetch_assoc();
        
        return $oTeam;
    }
}
