<?php

namespace App\Libs;

use App\Models\Operator;

class WorkLog
{
    const GREP_TAG      = '\[WorkLog\]';
    const FORMAT_STRING = "[WorkLog][%d:%s] %s "; // 



    /**
     * とりあえずこれを通して作業ログ出す形に。
     * 
     * @param  Operator       $operator
     * @param  string         $message
     * @return array          $extra
     */
    public static function log( Operator $operator, $message = '', $extra = [] )
    {
        // メッセージ組み立て
        $msg = self::makeMessage( $operator, $message );
        // ログに記録
        \Log::notice( $msg, $extra );
    }
    public static function makeMessage( Operator $operator, $message = '' )
    {
        return sprintf( self::FORMAT_STRING, $operator->id, $operator->name, $message );
    }


    /**
     * とりあえず全部返す感じで。
     * @param  string         $timestring
     * @param  int            $second
     * @return int
     */
    public static function getList()
    {
        // とりあえず全件

        // ログファイルのファイル名一覧取得
        $logs = []; // or [][]

        $dir   = storage_path('logs');
        $files = [];
        exec("ls -1r " . $dir . "|grep laravel", $files);
        if( empty($files) )
        {
            return $logs;
        }
        else
        {
            // 日づけ最新のものから処理
            foreach( $files as $file )
            {
                $filepath = $dir . "/" . $file;
                $output = [];
                // [WorkLog]をgrepする感じで文字列一覧取得
                exec("grep -E '".self::GREP_TAG."' ".$filepath, $output);

                // 最新が最初にくるように反転させる
                $log = array_reverse($output);

                // $listに追加
                $logs = array_merge($logs, $log);
            }
        }

        return $logs;
    }

}
