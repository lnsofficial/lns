<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_MODEL . "User.php" );

class TeamStaffs extends Base{
	const MAIN_TABLE			= "team_staffs";
	const COL_ID				= "id";
	
	// ƒJƒ‰ƒ€
	const DATA	= [
		"id"			    => [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
	];

}
