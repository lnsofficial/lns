<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "User.php" );

class TeamOwner extends Base{
	const MAIN_TABLE			= "team_owner";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"			    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];

	public static function getUserIdFromTeamId( $team_id ){
		$oDb = new Db();
		
		$ahsResult = static::getList( $oDb, [ [ "column" => "team_id",  "type" => "int", "value" => $team_id ] ] );
		if( $ahsResult ){
			$oUser = new User( $oDb, $ahsResult[0]["user_id"] );
		}
		
		return $oUser;
	}
}
