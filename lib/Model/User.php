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
	function getUserFromLoginId( $login_id ){
		$oDb = new Db();
		
		$sSelectUser = "SELECT * FROM users WHERE login_id = ?";
		$ahsParameter = [ $login_id ];
		
		$oResult = $oDb->executePrepare( $sSelectUser, "s", $ahsParameter );
		
		$oUser = null;
		while( $row = $oResult->fetch_array() ){
			$oUser = new User( $row["id"] );
			break;
		}
		
		return $oUser;
	}
}