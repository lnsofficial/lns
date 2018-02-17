<?php
require_once( PATH_MODEL . "Base.php" );

class ApiSettings extends Base{
    const MAIN_TABLE	= "api_settings";
    const COL_ID		= "id";
    
    // カラム
    const DATA	= [
        "id"            => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "setting_name"  => [ "type" => "varchar"    , "min" => 0    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "value"         => [ "type" => "varchar"    , "min" => 0    ,"max" => 127           , "required" => true    , "null" => false   ],
    ];

    /**
     * // 設定値取得
     * 
     * @param  string   $setting_name   // 設定名
     * @return 
     */
    public function getSettingValue( $setting_name ){
        if( empty( $setting_name ) ){
            return null;
        }
        
        $value = null;
        
        $oDb = new Db();
        $oUser = null;
        
        $ahsResult = ApiSettings::getList( $oDb, [ [ "column" => "setting_name",  "type" => "varchar", "value" => $setting_name ] ] );
        
        if( $ahsResult ){
            $value = $ahsResult[0]["value"];
        }
        
        return $value;
    }
}