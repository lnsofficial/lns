<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_LIB . '/common/UtilTime.php');

class LolSeason extends Base
{
	const MAIN_TABLE			= "lol_seasons";
	const COL_ID				= "id";

	// カラム
	const DATA	= [
		"id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"season"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"start_at"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"end_at"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"created_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"deleted_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
	];



    /**
     * get○○系 ：複数レコード期待できるやつ
     * find○○系：単一レコード期待できるやつ
     */



	/**
	 * // 現在のシーズンを取得
	 *
	 * @param  void
	 * @return LolSeason
	 */
	public function findSeason()
	{
		$now = UtilTime::now();
		$prepareSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE start_at <= ? AND ? <= end_at";
		$bindParam  = [
			$now,
			$now,
		];
		return $this->db->executePrepare( $prepareSql, "ss", $bindParam )->fetch_assoc();
	}


	/**
	 * // 現在の1個前のシーズンを取得
	 *
	 * @param  void
	 * @return LolSeason
	 */
	public function findBeforeSeason()
	{
		$season = $this->findSeason();
		if( $season['id'] > 1 )
		{
			$before_season_id = $season['id'] - 1;
			return $this->find($before_season_id);
		}
		else
		{
			// 前シーズンレコードが取れない。※errorログに出したい・・
			return null;
		}
	}


    /**
     * // pk検索1レコード
     * 
     * @param  int                  $id                     // lol_seasons.id
     * @return LolSeason
     */
    function find( $id )
    {
        $prepareSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE id = ?";
        $bindParam  = [$id];
        return $this->db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
    }

}
