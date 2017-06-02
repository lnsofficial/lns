<?php
require_once( PATH_MODEL . "Base.php" );

class Member extends Base{
	const MAIN_TABLE	= "m_member";
	const COL_ID		= "member_id";
	
	// カラム
	const DATA	= [
		"member_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"summoner_name"		=> [ "type" => "varchar"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];


}