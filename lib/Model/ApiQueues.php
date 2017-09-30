<?php
require_once( PATH_MODEL . "Base.php" );

class ApiQueues extends Base{
    const MAIN_TABLE	= "api_queues";
    const COL_ID		= "id";
    
    // カラム
    const DATA	= [
        "id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
        "action"			=> [ "type" => "int"		, "min" => 0	,"max" => 2147483647	, "required" => true	, "null" => false	],
        "state"				=> [ "type" => "int"		, "min" => 0	,"max" => 127			, "required" => true	, "null" => false	],
        "priority"			=> [ "type" => "int"		, "min" => -2147483648,"max"=>2147483647, "required" => true	, "null" => false	],
        "payload"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 255			, "required" => true	, "null" => true	],
        "result"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 65,535		, "required" => true	, "null" => true	],
    ];
    
    const ACTION_UPDATE_SUMMONER        = 1;  // サモナーネーム変更時のriotApiへの問い合わせ
    const ACTION_REGISTER_SUMMONER      = 2;  // ユーザー初回登録時のriotApiへの問い合わせ
    
    
    const STATE_UNTREATED               = 0;
    const STATE_DOING                   = 1;
    const STATE_FAILED                  = 2;
    const STATE_FINISHED                = 3;


}