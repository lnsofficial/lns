<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "UserRank.php" );

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

        $prepareSql = "SELECT tm.id AS id,tm.team_id AS team_id,tm.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role FROM team_members AS tm LEFT JOIN users AS u ON tm.user_id=u.id WHERE team_id = ? AND deleted_at IS NULL";
        $bindParam  = [ $team_id ];

        $result = $db->executePrepare( $prepareSql, "i", $bindParam );

        $team_members = [];
        while( $team_member = $result->fetch_assoc() )
        {
            $oUserRank = new UserRank( $db );
            $oUser = new User( $db, $team_member["user_id"] );
            $oLastApiQueue = $oUser->getLastApiQueue();
            $team_member["summoner_name_kana"] = $oUser->summoner_name_kana;
            $team_member["last_api_queue_state"] = $oLastApiQueue->state;
            $team_member["comment"] = $oUser->comment;
            $team_member["now_rank"] = $oUserRank->findByUserId($team_member['user_id']);
            $team_member["before_rank"] = $oUserRank->findBeforeSeasonByUserId($team_member['user_id']);
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

        $prepareSql = "SELECT tm.id AS id,tm.team_id AS team_id,tm.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role FROM team_members AS tm LEFT JOIN users AS u ON tm.user_id=u.id WHERE user_id = ? AND deleted_at IS NULL";
        $bindParam  = [ $user_id ];

        $result = $db->executePrepare( $prepareSql, "i", $bindParam );

        return $result->fetch_assoc();
    }

}
