<?php
require_once( PATH_MODEL . "Base.php" );

class TeamContact extends Base{
	const MAIN_TABLE			= "teams_contact";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"			    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];

    function getUserIdFromTeamId( $team_id ){
        $oDb = new Db();

        $sSelectUser = "SELECT * FROM teams_contact WHERE team_id = ?";
        $ahsParameter = [ $team_id ];
        
        $oResult = $oDb->executePrepare( $sSelectUser, "s", $ahsParameter );
        
        $oUsers = $oResult->fetch_assoc();
        
        return $oUsers["user_id"];
    }


    /**
     * // そのチームの連絡者の一覧
     * 
     * @param  int                  $team_id                // teams.id
     * @return (TeamContact + User)[]
     */
    function getByTeamId( $team_id )
    {
        $db = new Db();

        $prepareSql = "SELECT tc.id AS id,tc.team_id AS team_id,tc.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role FROM teams_contact AS tc LEFT JOIN users AS u ON tc.user_id=u.id WHERE team_id = ? AND tc.deleted_at IS NULL";
        $bindParam  = [ $team_id ];

        $result = $db->executePrepare( $prepareSql, "i", $bindParam );

        $user_team_offers = [];
        while( $offer = $result->fetch_assoc() )
        {
            $user_team_offers[] = $offer;
        }

        return $user_team_offers;
    }


    /**
     * // user_id, team_id 検索1レコード
     * 
     * @param  int                  $user_id                // users.id
     * @param  int                  $team_id                // teams.id
     * @return TeamContact
     */
    function findByUserIdTeamId( $user_id, $team_id )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM teams_contact WHERE user_id = ? AND team_id = ? AND deleted_at IS NULL";
        $bindParam  = [
            $user_id,
            $team_id,
        ];
        return $db->executePrepare( $prepareSql, "ii", $bindParam )->fetch_assoc();
    }

}
