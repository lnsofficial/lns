<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "Teams.php" );
require_once( PATH_MODEL . "TeamMembers.php" );
require_once( PATH_MODEL . "ApiQueues.php" );

class User extends Base{
	const MAIN_TABLE	= "users";
	const COL_ID		= "id";
	
	const RANK_LIST = [
	    "CHALLENGER" => [ "I" => 100 ],
	    "MASTER"     => [ "I" => 94  ],
	    "DIAMOND"    => [ "I" => 88, "II" => 83, "III" => 78, "IV" => 73, "V" => 68],
	    "PLATINUM"   => [ "I" => 63, "II" => 60, "III" => 57, "IV" => 54, "V" => 51],
	    "GOLD"       => [ "I" => 48, "II" => 45, "III" => 42, "IV" => 39, "V" => 36],
	    "SILVER"     => [ "I" => 33, "II" => 31, "III" => 29, "IV" => 27, "V" => 25],
	    "BRONZE"     => [ "I" => 23, "II" => 22, "III" => 21, "IV" => 20, "V" => 10],
	];
	
	// カラム
	const DATA	= [
		"id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"login_id"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"password"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"summoner_id"	=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"account_id"	=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"tier"	        => [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"rank"	        => [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
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
			$oUser = new User( $oDb, $ahsResult[0]["id"] );
		}
		
		return $oUser;
	}
	
	public static function getUserFromDiscordId( $discord_id ){
		$oDb = new Db();
		$oUser = null;
		
		$ahsResult = static::getList( $oDb, [ [ "column" => "discord_id",  "type" => "varchar", "value" => $discord_id ] ] );
		if( $ahsResult ){
			$oUser = new User( $oDb, $ahsResult[0]["id"] );
		}
		
		return $oUser;
	}
	
	public static function getUserFromSummonerName( $summoner_name ){
		$oDb = new Db();
		$oUser = null;
		
		$ahsResult = static::getList( $oDb, [ [ "column" => "summoner_name",  "type" => "varchar", "value" => $summoner_name ] ] );
		if( $ahsResult ){
			$oUser = new User( $oDb, $ahsResult[0]["id"] );
		}
		
		return $oUser;
	}
	
	public function getTeam(){
		$oDb = new Db();
		$oTeam = null;
		
		$ahsResult = TeamMembers::getList( $oDb, [ [ "column" => "user_id",  "type" => "int", "value" => $this->id ] ] );
		
		if( $ahsResult ){
			$oTeam = new Teams( $oDb, $ahsResult[0]["team_id"] );
		}
		return $oTeam;
	}
	
	function getLastApiQueue(){
		$oDb = new Db();
		$oApiQueue = null;
		
		// TODO 複数ユーザーまとめて動かすバッチになってきたら手直しいるかも
		$ahsParameter = [ [ "column" => "payload",  "type" => "varchar", "value" => json_encode( [ "user_id" => $this->id ] ) ] ];
		$ahsOrder     = [ [ "column" => "id", "sort_order" => "DESC" ] ];
		$ahsResult = ApiQueues::getList( $oDb, $ahsParameter, $ahsOrder );
		
		if( $ahsResult ){
			$oApiQueue = new ApiQueues( $oDb, $ahsResult[0]["id"] );
		}
		return $oApiQueue;
	}


    /**
     * // user_idで検索。
     * 
     * @param  int                  $user_id                // users.id
     * @return User + TeamMember + TeamOwner + TeamStaff + UserTeamApply + Team
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
        $prepareSql  = "SELECT * FROM team_staffs WHERE user_id = ?";
        $bindParam   = [ $user_id ];
		$result      = $db->executePrepare( $prepareSql, "i", $bindParam );
        $team_staffs = [];
		while( $team_staff = $result->fetch_assoc() )
		{
			$team_staffs[] = $team_staff;
		}
//var_dump($team_staffs);

		// TeamContact
        $prepareSql  = "SELECT * FROM teams_contact WHERE user_id = ?";
        $bindParam   = [ $user_id ];
		$result      = $db->executePrepare( $prepareSql, "i", $bindParam );
        $team_contacts = [];
		while( $team_contact = $result->fetch_assoc() )
		{
			$team_contacts[] = $team_contact;
		}

		// Team
		$team = false;
		if( !empty($team_member) )
		{
			$team = Teams::find( $team_member['team_id'] );
		}
		// UserTeamApply
		$prepareSql  = "SELECT * FROM user_team_applys WHERE user_id = ? AND deleted_at IS NULL";
		$bindParam   = [ $user_id ];
		$result      = $db->executePrepare( $prepareSql, "i", $bindParam );
		$user_team_applys = [];
		while( $user_team_apply = $result->fetch_assoc() )
		{
			$user_team_applys[] = $user_team_apply;
		}
		$user['team_member']      = $team_member;
		$user['team_owners']      = $team_owners;
		$user['team_staffs']      = $team_staffs;
		$user['team_contacts']    = $team_contacts;
		$user['team']             = $team;
		$user['user_team_applys'] = $user_team_applys;
		
        return $user;
    }

}