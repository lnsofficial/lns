<?php
require_once( PATH_MODEL . "Base.php" );

class User extends Base{
	const MAIN_TABLE	= "users";
	const COL_ID		= "id";
	
	// カラム
	const DATA	= [
		"id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"login_id"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"password"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"summoner_id"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"summoner_name"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"discord_id"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"main_role"		=> [ "type" => "int"		, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"main_champion"	=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
	
	// TODO 特定カラムによる検索を共通化
	public static function getUserFromLoginId( $login_id ){
		$oDb = new Db();
		$oUser = null;
		
		$ahsResult = static::getList( $oDb, [ [ "column" => "login_id",  "type" => "varchar", "value" => $login_id ] ] );
		if( $ahsResult ){
			$oUser = new User( $oDb, $ahsResult["id"] );
		}
		
		return $oUser;
	}
	
	public static function getUserFromDiscordId( $discord_id ){
		$oDb = new Db();
		$oUser = null;
		
		$ahsResult = static::getList( $oDb, [ [ "column" => "discord_id",  "type" => "varchar", "value" => $discord_id ] ] );
		if( $ahsResult ){
			$oUser = new User( $oDb, $ahsResult["id"] );
		}
		
		return $oUser;
	}
	
	public static function getUserFromSummonerName( $summoner_name ){
		$oDb = new Db();
		$oUser = null;
		
		$ahsResult = static::getList( $oDb, [ [ "column" => "summoner_name",  "type" => "varchar", "value" => $summoner_name ] ] );
		if( $ahsResult ){
			$oUser = new User( $oDb, $ahsResult["id"] );
		}
		
		return $oUser;
	}


    /**
     * // user_idで検索。
     * 
     * @param  int                  $user_id                // users.id
     * @return User + TeamMember + TeamOwner + TeamStaff + UserTeamApply
     */
	function info( $user_id )
    {
        $db = new Db();

//      $prepareSql = "SELECT tm.id AS id,tm.team_id AS team_id,tm.user_id AS user_id,u.summoner_id AS summoner_id,u.summoner_name AS summoner_name,u.discord_id AS discord_id,u.main_role AS main_role,u. main_champion AS  main_champion FROM team_members AS tm LEFT JOIN users AS u ON tm.user_id=u.id WHERE user_id = ?";
		// User
        $prepareSql = "SELECT * FROM users WHERE id = ?";
        $bindParam  = [ $user_id ];
        $user = $db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
//var_dump($user);
		if( empty($user) )
		{
			return false;
		}

		// TeamMember
//      $db = new Db();
        $prepareSql  = "SELECT * FROM team_members WHERE user_id = ?";
        $bindParam   = [ $user_id ];
        $team_member = $db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
//var_dump($team_member);

		// TeamOwner
//      $db = new Db();
        $prepareSql  = "SELECT * FROM team_owner WHERE user_id = ?";
        $bindParam   = [ $user_id ];
		$result      = $db->executePrepare( $prepareSql, "i", $bindParam );
        $team_owners = [];
		while( $team_owner = $result->fetch_assoc() )
		{
			$team_owners[] = $team_owner;
		}
//var_dump($team_owners);

		// TeamStaff
//      $db = new Db();
        $prepareSql  = "SELECT * FROM team_owner WHERE user_id = ?";
        $bindParam   = [ $user_id ];
		$result      = $db->executePrepare( $prepareSql, "i", $bindParam );
        $team_staffs = [];
		while( $team_staff = $result->fetch_assoc() )
		{
			$team_staffs[] = $team_staff;
		}
//var_dump($team_staffs);

		$user['team_member'] = $team_member;
		$user['team_owners'] = $team_owners;
		$user['team_staffs'] = $team_staffs;

        return $user;
    }

}