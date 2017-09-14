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
}