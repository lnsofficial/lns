<?php
require_once( PATH_MODEL . "Base.php" );

class LoginAccount extends Base{
	const MAIN_TABLE	= "login_account";
	const COL_ID		= "account_id";
	
	// カラム
	const DATA	= [
		"account_id"	=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"		=> [ "type" => "int"		, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"login_id"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"passwd"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"state"			=> [ "type" => "int"		, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
}