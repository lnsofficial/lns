<?php
require_once( PATH_MODEL . "Base.php" );

class TeamJoin extends Base{
	const MAIN_TABLE	= "team_joins";
	const COL_ID		= "id";
	
	// カラム
	const DATA	= [
		"id"			=> [ "type" => "int"	, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"		=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"match_id"		=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"joined_at"		=> [ "type" => "date"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"state"			=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
	
	const STATE_CANCEL = 0;
	const STATE_ENABLE = 1;
}
