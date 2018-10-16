<?php

namespace App\Models;

use App\Libs\UtilTime;

class LolSeason extends BaseModel
{
    protected $table      = 'lol_seasons';  // テーブル名
    protected $guarded    = [];

    protected static $season_now    = '';
    protected static $season_before = '';



	/**
	 * 現在のシーズンを取得
	 *
	 *
	 */
	public static function findSeason()
	{
		if( !empty(self::$season_now) )
		{
			return self::$season_now;
		}

		$now = UtilTime::now();
		$record = self::where('start_at', '<=', $now)
					  ->where('end_at',   '>=', $now)
					  ->first();
		self::$season_now = $record;

		return $record;
	}

	/**
	 * 現在の1個前のシーズンを取得
	 *
	 *
	 */
	public static function findBeforeSeason()
	{
		if( !empty(self::$season_before) )
		{
			return self::$season_before;
		}

		$season = self::findSeason();
		if( $season->id > 1 )
		{
			$before_season_id = $season->id - 1;
			$record = self::find($before_season_id);
			self::$season_before = $record;

			return $record;
		}
		else
		{
			\Log::error('前シーズンレコードが取れない。$season = '.print_r($season->toArray(),true));
			return null;
		}
	}

}
