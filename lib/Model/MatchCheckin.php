<?php
require_once( PATH_MODEL . "Base.php" );

class MatchCheckin extends Base
{
	const MAIN_TABLE			= "match_checkins";
	const COL_ID				= "id";

	// カラム
	const DATA	= [
		"id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"match_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],

		"summoner_id"		=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"champion_id"		=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],

		"created_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 65535			, "required" => true	, "null" => false	],
		"updated_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 65535			, "required" => true	, "null" => false	],
	];



    /**
     * get○○系 ：複数レコード期待できるやつ
     * find○○系：単一レコード期待できるやつ
     */



    /**
     * // 3つ指定。
     * 
     * @param  int                  $match_id               // matches.id
     * @param  int                  $team_id                // teams.id
     * @param  int                  $user_id                // users.id
     * @return MatchCheckin
     */
    static function findByMatchIdTeamIdUserId( $match_id, $team_id, $user_id )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM " . static::MAIN_TABLE . " WHERE match_id = ? AND team_id = ? AND user_id = ?";
        $bindParam  = [
            $match_id,
            $team_id,
            $user_id,
        ];
        return $db->executePrepare( $prepareSql, "iii", $bindParam )->fetch_assoc();
    }


    /**
     * // チームのチェックイン一覧
     * 
     * @param  int                  $match_id               // matches.id
     * @param  int                  $team_id                // teams.id
     * @return MatchCheckin[]
     */
    static function getByMatchIdTeamId( $match_id, $team_id )
    {
        $db = new Db();

        $prepareSql = "SELECT * FROM " . static::MAIN_TABLE . " WHERE match_id = ? AND team_id = ?";
        $bindParam  = [ $match_id, $team_id ];

        $result = $db->executePrepare( $prepareSql, "ii", $bindParam );

        $match_checkins = [];
        while( $match_checkin = $result->fetch_assoc() )
        {
            $match_checkins[] = $match_checkin;
        }

        return $match_checkins;
    }
}
