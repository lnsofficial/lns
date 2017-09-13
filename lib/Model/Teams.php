<?php
require_once( PATH_MODEL . "Base.php" );

class Teams extends Base{
	const MAIN_TABLE			= "teams";
	const COL_ID				= "id";
	
	// カラム
	const DATA	= [
		"id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_name"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_name_kana"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"team_tag"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"team_tag_kana"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => false	, "null" => true	],
		"status"		    => [ "type" => "tinyint"	, "min" => 0	,"max" => 127			, "required" => false	, "null" => false	],
	];


    /**
     * // 作成者で検索、だと思う。
     * 
     * @param  int                  $user_id                // users.id
     * @return Team
     */
    function getTeamFromUserId( $user_id )
    {
        $oDb = new Db();

        $sSelectUser = "SELECT * FROM teams WHERE user_id = ?";
        $ahsParameter = [ $user_id ];
        
        $oResult = $oDb->executePrepare( $sSelectUser, "i", $ahsParameter );
        
        $oTeam = $oResult->fetch_assoc();
        
        return $oTeam;
    }



    /**
     * // pk検索1レコード
     * 
     * @param  int                  $id                     // teams.id
     * @return Team
     */
    function find( $id )
    {
        $db = new Db();
        $prepareSql = "SELECT * FROM teams WHERE id = ?";
        $bindParam  = [$id];
        return $db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
    }

}
