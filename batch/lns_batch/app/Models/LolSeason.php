<?php

namespace App\Models;

use App\Libs\UtilTime;

class LolSeason extends BaseModel
{
    protected $table      = 'lol_seasons';  // テーブル名
    protected $guarded    = [];



	/**
	 * 現在のシーズンを取得
	 *
	 *
	 */
	public static function findSeason()
	{
		$now = UtilTime::now();
		return self::where('start_at', '<=', $now)
				   ->where('end_at',   '>=', $now)
				   ->first();
	}

	/**
	 * 現在の1個前のシーズンを取得
	 *
	 *
	 */
	public static function findBeforeSeason()
	{
		$season = self::findSeason();
		if( $season->id > 1 )
		{
			$before_season_id = $season->id - 1;
			return self::find($before_season_id);
		}
		else
		{
			\Log::error('前シーズンレコードが取れない。$season = '.print_r($season->toArray(),true));
			return null;
		}
	}

}
