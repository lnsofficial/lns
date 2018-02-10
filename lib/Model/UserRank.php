<?php
require_once( PATH_MODEL . "Base.php" );
require_once( PATH_LIB . '/common/UtilTime.php');
require_once( PATH_MODEL . "LolSeason.php" );

class UserRank extends Base
{
	const MAIN_TABLE			= "user_ranks";
	const COL_ID				= "id";

	// カラム
	const DATA	= [
		"id"				=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"season"			=> [ "type" => "varchar"	, "min" => 0	,"max" => 256			, "required" => true	, "null" => false	],
		"user_id"			=> [ "type" => "int"		, "min" => 1	,"max" => 2147483647	, "required" => true	, "null" => false	],
		"tier"				=> [ "type" => "varchar"    , "min" => 0    ,"max" => 256           , "required" => true    , "null" => false   ],
		"rank"				=> [ "type" => "varchar"    , "min" => 0    ,"max" => 256           , "required" => true    , "null" => false   ],
		"created_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 65535			, "required" => true	, "null" => false	],
		"deleted_at"		=> [ "type" => "varchar"	, "min" => 0	,"max" => 65535			, "required" => true	, "null" => false	],
	];



    /**
     * get○○系 ：複数レコード期待できるやつ
     * find○○系：単一レコード期待できるやつ
     */




	/**
	 * // 現シーズン/前シーズンのうち高い方のランクのレコードを返す
	 *
	 *
	 */
	public function findHigherByUserId( $user_id )
	{
		$user_rank_now    = $this->findByUserId            ( $user_id );
		$user_rank_before = $this->findBeforeSeasonByUserId( $user_id );

		if( empty($user_rank_now) || $user_rank_now['tier'] == 'UNRANK' )
		{
			return $user_rank_before;
		}
		else if( empty($user_rank_before) || $user_rank_before['tier'] == 'UNRANK' )
		{
			return $user_rank_now;
		}
		else
		{
			$point_now    = User::RANK_LIST[$user_rank_now['tier']][$user_rank_now['rank']];
			$point_before = User::RANK_LIST[$user_rank_before['tier']][$user_rank_before['rank']];

			return ($point_now >= $point_before) ? $user_rank_now : $user_rank_before;
		}
	}


	/**
	 * // 現シーズンのレコードを取得
	 *
	 *
	 */
	public function findByUserId( $user_id )
	{
		$oLolSeason = new LolSeason( $this->db );
		$season     = $oLolSeason->findSeason();

		$prepareSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE season = ? AND user_id = ?";
		$bindParam  = [
			$season['season'],
			$user_id,
		];
		return $this->db->executePrepare( $prepareSql, "si", $bindParam )->fetch_assoc();
	}


	/**
	 * // 現在の1個前のシーズンのレコードを取得
	 *
	 *
	 */
	public function findBeforeSeasonByUserId( $user_id )
	{
		$oLolSeason = new LolSeason( $this->db );
		$season     = $oLolSeason->findBeforeSeason();
		if( !empty($season) )
		{
			$prepareSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE season = ? AND user_id = ?";
			$bindParam  = [
				$season['season'],
				$user_id,
			];
			return $this->db->executePrepare( $prepareSql, "si", $bindParam )->fetch_assoc();
		}
		else
		{
			// 前シーズンレコードが取れないので現シーズンで
			return $this->findByUserId( $user_id );
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
        $prepareSql = "SELECT * FROM " . self::MAIN_TABLE . " WHERE id = ? AND deleted_at IS NULL";
        $bindParam  = [$id];
        return $this->db->executePrepare( $prepareSql, "i", $bindParam )->fetch_assoc();
    }

}
