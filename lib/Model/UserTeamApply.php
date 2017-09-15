<?php
require_once( PATH_MODEL . "Base.php" );

class UserTeamApply extends Base
{
	const MAIN_TABLE			= "user_team_applys";
	const COL_ID				= "id";

	const TYPE_MEMBER           = 1;
	const TYPE_CONTACT          = 2;
	const TYPE_STAFF            = 3;

	const STATE_APPLY           = 1; // ユーザーがチームへ申請中
	const STATE_CANCEL          = 2; // ユーザーがチームへの申請を取り下げた
	const STATE_ACCEPT          = 3; // チームが申請を受諾した
	const STATE_DENY            = 4; // チームが申請を拒否した


	// カラム
	const DATA	= [
		"id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"type"				=> [ "type" => "int"		, "min" => 0	,"max" => 255			, "required" => true	, "null" => false	],
		"state"				=> [ "type" => "int"		, "min" => 0	,"max" => 255			, "required" => true	, "null" => false	],
		"deleted_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 65535			, "required" => true	, "null" => false	],
	];



    /**
     * get○○系 ：複数レコード期待できるやつ
     * find○○系：単一レコード期待できるやつ
     */



    /**
     * // 3つ指定かつdeleted_atがNULLじゃないやつ。
     * 
     * @param  int                  $user_id                // users.id
     * @param  int                  $team_id                // teams.id
     * @param  int                  $state
     * @return UserTeamApply
     */
    function findByUserIdTeamIdState( $user_id, $team_id, $state )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM user_team_applys WHERE deleted_at IS NULL AND user_id = ? AND team_id = ? AND state = ?";
        $bindParam  = [
            $user_id,
            $team_id,
            $state,
        ];
        return $db->executePrepare( $prepareSql, "iii", $bindParam )->fetch_assoc();
    }


    /**
     * // そのチームへの応募の一覧
     * // とりあえずSTATE_CANCELとかDENYも含める形で。
     * // 論理削除済みのdeleted_at IS NULLは含めない形で。
     * 
     * @param  int                  $team_id                // teams.id
     * @return UserTeamApply[]
     */
    function getByTeamId( $team_id )
    {
        $db = new Db();

        $prepareSql = "SELECT uta.id AS id,uta.team_id AS team_id,uta.user_id AS user_id,uta.type AS type,uta.state AS state,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role,u. main_champion AS  main_champion FROM user_team_applys AS uta LEFT JOIN users AS u ON uta.user_id=u.id WHERE team_id = ? AND deleted_at IS NULL";
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
     * // pk検索1レコード
     * 
     * @param  int                  $id                     // user_team_offers.id
     * @return UserTeamApply
     */
    function find( $id )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM user_team_applys WHERE id = ? AND deleted_at IS NULL";
        $bindParam  = [$id];
        return $db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
    }

}
