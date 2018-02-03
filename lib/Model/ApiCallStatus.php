<?php

require_once( PATH_MODEL  . "Base.php" );

class ApiCallStatus extends Base{
    const MAIN_TABLE	= "api_call_statuses";
    const COL_ID		= "id";
    
    // カラム
    const DATA	= [
        "id"                    => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "apikey"                => [ "type" => "varchar"    , "min" => 0    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "last_reset_by_1sec"    => [ "type" => "date"       , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "last_reset_by_2min"    => [ "type" => "date"       , "min" => 1    ,"max" => 256           , "required" => true    , "null" => false   ],
        "count_by_1sec"         => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
        "count_by_2min"         => [ "type" => "int"        , "min" => 1    ,"max" => 2147483647    , "required" => true    , "null" => false   ],
    ];

    const LIMIT_BY_1SEC     = 5;
    const LIMIT_BY_2MIN     = 60;

    public static function getStatus( $oDb, $apikey){
        $ahsResult = static::getList( $oDb, [ [ "column" => "apikey",  "type" => "varchar", "value" => $apikey ] ] );
        
        if( $ahsResult ){
            $status = new ApiCallStatus($oDb, $ahsResult[0]["id"]);
        } else {
            $status = new ApiCallStatus($oDb);
            
            $now = date( 'Y-m-d H:i:s' );
            $status->apikey             = $apikey;
            $status->last_reset_by_1sec = $now;
            $status->last_reset_by_2min = $now;
            
            $status->save();
        }
        
        return $status;
    }

    public function checkLimit( $count = true ){
        // 現在時刻取得しておく。(UnixTimestamp値で。)
        $now               = time();
        $enable            = true;
        $waittime_for_1sec = 0;
        $waittime_for_2min = 0;

        // ==== 1秒について。 ==== 
        if( strtotime( $this->last_reset_by_1sec . " + 1 seconds" ) <= $now ){
            // 1秒経っているのでカウントをリセット
            $this->last_reset_by_1sec = date( 'Y-m-d H:i:s', $now );
            $this->count_by_1sec      = 1;
        }else if( $this->count_by_1sec < ApiCallStatus::LIMIT_BY_1SEC ){
            // リミット未満なのでセーフ、回数は増やす
            $this->count_by_1sec++;
        }else{
            // アウアウ。
            $enable = false;
            $waittime_for_1sec = strtotime( $this->last_reset_by_1sec . " + 1 seconds" ) + 1 - $now; // バッファで1秒追加。
        }

        // ==== 2分について。 ==== 
        if( strtotime( $this->last_reset_by_2min . " + 2 minutes" ) <= $now ){
            // 2分経っているのでカウントをリセット
            $this->last_reset_by_2min = date( 'Y-m-d H:i:s', $now );
            $this->count_by_2min      = 1;
        }else if( $this->count_by_2min < self::LIMIT_BY_2MIN ){
            // リミット未満なのでセーフ
            $this->count_by_2min++;
        }else{
            // アウアウ。
            $enable = false;
            $waittime_for_2min = strtotime( $this->last_reset_by_2min . " + 2 minutes" ) + 1 - $now; // バッファで1秒追加。
        }

        // 1秒制限、2分制限の両方が問題なければ、カウントアップ(＋時間リセットしてればそれも)をアップデートしておく
        if( $enable && $count ){
            $this->save();
        }

        $res = [
            'id'                 => $this->id,
            'apikey'             => $this->apikey,
            'last_reset_by_1sec' => $this->last_reset_by_1sec,
            'last_reset_by_2min' => $this->last_reset_by_2min,
            'count_by_1sec'      => $this->count_by_1sec,
            'count_by_2min'      => $this->count_by_2min,
            'created_at'         => $this->created_at,
            'updated_at'         => $this->updated_at,
            'enable'             => $enable,
            'waittime_for_1sec'  => $waittime_for_1sec,
            'waittime_for_2min'  => $waittime_for_2min,
        ];
        return $res;
    }
}
