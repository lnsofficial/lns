<?php

class UtilTime
{
    const FORMAT_STRING = "Y-m-d H:i:s"; // 日付けフォーマット
    const WEEK_LIST     = [
        "日",
        "月",
        "火",
        "水",
        "木",
        "金",
        "土",
    ];


    public static function now()
    {
        return date( self::FORMAT_STRING );
    }

    public static function nowTime()
    {
        return strtotime(self::now());
    }

    public static function timeToStr( $time )
    {
        return date( self::FORMAT_STRING, $time );
    }

    public static function timeToStrForMatchList( $timestr )
    {
        $week_str = self::weekStr($timestr);
        $time     = strtotime($timestr);
        $md       = date('n月j日', $time );
        $hm       = date('H:i',    $time );

        return $md . "(" . $week_str . ") " . $hm;
    }

	/**
	 * // 渡された日付け文字列に指定秒を足したUnixTimestampを返す
	 * @param  string         $timestring
	 * @param  int            $second
	 * @return int
	 */
	public static function addSecond($timestring, $second)
	{
		return strtotime($timestring) + $second;
	}
	public static function addMinutes($timestring, $minutes)
	{
		return self::addSecond($timestring, $minutes*60);
	}
	public static function weekStr($timestring)
	{
		// 0 (日曜)から 6 (土曜)
		$w = date("w", strtotime($timestring));
		return self::WEEK_LIST[$w];
	}

}
