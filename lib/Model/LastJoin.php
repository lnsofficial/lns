<?php
require_once( PATH_MODEL . "Base.php" );

class LastJoin extends Base{
	const MAIN_TABLE	= "t_last_join";
	const COL_ID		= "last_join_id";
	
	// カラム
	const DATA	= [
		"last_join_id"	=> [ "type" => "int"	, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"team_id"		=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"match_id"		=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"join_date"		=> [ "type" => "date"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
		"state"			=> [ "type" => "int"	, "min" => 1	,"max" => 256			, "required" => true	, "null" => false	],
	];
	
	const STATE_CANCEL = 0;
	const STATE_ENABLE = 1;
}
