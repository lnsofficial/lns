<?php
require_once( PATH_MODEL . "Base.php" );

class ManagementObservers extends Base{
    const MAIN_TABLE	= "management_observers";
    const COL_ID		= "id";
    
    // カラム
    const DATA	= [
        "id"            => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "summoner_id"   => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
    ];
    
    public function getAllObservers(){
        $oDb = new Db();
        
        $ahsResult = ManagementObservers::getList( $oDb, [ [ "column" => "id",  "type" => "int", "operator" => ">", "value" => 0 ] ] );
        
        return $ahsResult;
    }
    
}