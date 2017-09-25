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



    /**
     * // team_idで検索。
     * 
     * @param  int                  $team_id                // teams.id
     * @return (TeamMember + User)[]
     */
    function getByTeamId( $team_id )
    {
        $db = new Db();

        $prepareSql = "SELECT tm.id AS id,tm.team_id AS team_id,tm.user_id AS user_id,u.summoner_id AS summoner_id,u.tier AS tier,u.rank AS rank,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role,u. main_champion AS  main_champion FROM team_members AS tm LEFT JOIN users AS u ON tm.user_id=u.id WHERE team_id = ?";
        $bindParam  = [ $team_id ];

        $result = $db->executePrepare( $prepareSql, "i", $bindParam );

        $team_members = [];
        while( $team_member = $result->fetch_assoc() )
        {
            $team_members[] = $team_member;
        }

        return $team_members;
    }



    /**
     * // user_idで検索。
     * 
     * @param  int                  $user_id                // users.id
     * @return TeamMember + User
     */
	function findByUserId( $user_id )
    {
        $db = new Db();

        $prepareSql = "SELECT tm.id AS id,tm.team_id AS team_id,tm.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role,u. main_champion AS  main_champion FROM team_members AS tm LEFT JOIN users AS u ON tm.user_id=u.id WHERE user_id = ?";
        $bindParam  = [ $user_id ];

        $result = $db->executePrepare( $prepareSql, "i", $bindParam );

        return $result->fetch_assoc();
    }

}
