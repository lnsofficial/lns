<?php
require_once( PATH_MODEL . "Base.php" );

class ApiQueues extends Base{
	const MAIN_TABLE	= "api_queues";
	const COL_ID		= "id";
	
	// カラム
	const DATA	= [
		"member_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"action"			=> [ "type" => "int"		, "min" => 0	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"state"				=> [ "type" => "int"		, "min" => 0	,"max" => 127			, "required" => true	, "null" => false	],
		"priority"			=> [ "type" => "int"		, "min" => -2147483648,"max"=>2147483647, "required" => true	, "null" => false	],
		"payload"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 255			, "required" => true	, "null" => true	],
		"result"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 65,535		, "required" => true	, "null" => true	],
	];


}