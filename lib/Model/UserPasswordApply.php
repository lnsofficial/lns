<?php
require_once( PATH_MODEL . "Base.php" );

class UserPasswordApply extends Base{
    const MAIN_TABLE    = "user_password_apply";
    const COL_ID        = "id";

    const STATE_APPLY           = 1; // ユーザーがパスワード変更を申請中
    const STATE_SUCCESS         = 2; // ユーザーがパスワード変更の申請に成功
    const STATE_ERROR           = 3; // ユーザーがパスワード変更の申請に失敗


    // カラム
    const DATA	= [
        "id"        => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "user_id"   => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "icon_id"   => [ "type" => "int"        , "min" => 0    ,"max" => 255           , "required" => true    , "null" => false   ],
        "code"      => [ "type" => "varchar"    , "min" => 0    ,"max" => 255           , "required" => true    , "null" => false   ],
        "state"     => [ "type" => "int"        , "min" => 0    ,"max" => 255           , "required" => true    , "null" => false   ],
    ];

    public static function getUserPasswordApplyByUserIdCode( $oDb, $user_id, $code ){
        $oUserPasswordApply = null;
        
        $ahsResult = static::getList( $oDb, [
                [ "column" => "user_id" , "type" => "int"     , "value" => $user_id ],
                [ "column" => "code"    , "type" => "varchar" , "value" => $code ],
                [ "column" => "state"   , "type" => "varchar" , "value" => static::STATE_APPLY ],
            ]
        );
        
        if( $ahsResult ){
            $oUserPasswordApply = new UserPasswordApply( $oDb, $ahsResult[0]["id"] );
        }
        
        return $oUserPasswordApply;
    }

}