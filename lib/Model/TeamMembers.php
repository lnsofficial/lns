<?php
require_once( PATH_MODEL . "Base.php" );

class TeamMembers extends Base{
	const MAIN_TABLE			= "team_members";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"			    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];

	function getByTeamId( $team_id )
	{
		$db = new Db();

		$prepareSql = "SELECT tm.id AS id,tm.team_id AS team_id,tm.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role,u. main_champion AS  main_champion FROM team_members AS tm LEFT JOIN users AS u ON tm.user_id=u.id WHERE team_id = ?";
		$bindParam  = [ $team_id ];

		$result = $db->executePrepare( $prepareSql, "i", $bindParam );

		$team_members = [];
		while( $team_members[] = $result->fetch_assoc() )
		{
		}
		if( count($team_members) > 1 && $team_members[0] === false )
		{
			return [];
		}

		return $team_members;
	}
}
