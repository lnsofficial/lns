<?php
require_once( PATH_MODEL . "Base.php" );

class AprilEnquete extends Base{
    const MAIN_TABLE    = "april_enquete";
    const COL_ID        = "id";
    
    // ƒJƒ‰ƒ€
    const DATA  = [
        "id"            => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "boin"          => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "cinderella"    => [ "type" => "int"        , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
    ];
  
}
