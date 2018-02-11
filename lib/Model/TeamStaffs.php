<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "User.php" );

class TeamStaffs extends Base{
	const MAIN_TABLE			= "team_staffs";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"			    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];



    /**
     * // そのチームのアナリストの一覧
     * 
     * @param  int                  $team_id                // teams.id
     * @return (TeamStaffs + User)[]
     */
    function getByTeamId( $team_id )
    {
        $db = new Db();

        $prepareSql = "SELECT ts.id AS id,ts.team_id AS team_id,ts.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role FROM team_staffs AS ts LEFT JOIN users AS u ON ts.user_id=u.id WHERE team_id = ?";
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
        $prepareSql = "SELECT * FROM team_staffs WHERE user_id = ? AND team_id = ?";
        $bindParam  = [
            $user_id,
            $team_id,
        ];
        return $db->executePrepare( $prepareSql, "ii", $bindParam )->fetch_assoc();
    }

}
