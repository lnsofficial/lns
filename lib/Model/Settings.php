<?php
require_once( PATH_MODEL . "Base.php" );

class Settings extends Base{
    const MAIN_TABLE    = "settings";
    const COL_ID        = "id";
    
    // カラム
    const DATA    = [
        "id"        => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "name"      => [ "type" => "varchar"    , "min" => 0    ,"max" => 256           , "required" => true    , "null" => true    ],
        "value"     => [ "type" => "varchar"    , "min" => 0    ,"max" => 256           , "required" => true    , "null" => true    ],
    ];
    
    const SEASON_START_DATE = "season_start";   // シーズン開始日
    const SEASON_END_DATE   = "season_end";     // シーズン終了日
    
    
    public function getSettingValue( $setting_name ){
        $oDb = new Db();
        $sSelectSettingSql = "SELECT * FROM " . self::MAIN_TABLE ." WHERE name = ?";
        $ahsParameter = [ $setting_name ];
        
        $oResult = $oDb->executePrepare( $sSelectSettingSql, "s", $ahsParameter );
        
        $setting_value = null;
        while( $row = $oResult->fetch_array() ){
            $setting_value = $row["value"];
            break;
        }
        
        return $setting_value;
    }

}