<?php
namespace App\Libs;

use \Exception as Exception;
use App\Models\LolSeason;

/**
 * ここに説明書く
 * 
 * 
 * 
 */
class OpggWebpage
{
	protected $base_url = "http://jp.op.gg/summoner/userName=";
	protected $summoner_name = '';
	protected $html          = '';

	/**
	 * // コンストラクタ。サモナー名を指定して生成する感じ。
	 *    webページの取得までやってしまう。
	 *
	 * @param  string                   $summoner_name
	 * @return void
	 */
	public function __construct( $summoner_name='' )
	{
		if( empty($summoner_name) )
		{
			\Log::error('インスタンス作成時に$summoner_nameの指定必須');
			throw new Exception('インスタンス作成時に$summoner_nameの指定必須');
		}
		$this->summoner_name = $summoner_name;
		$this->html          = file_get_contents( $this->base_url . rawurlencode($summoner_name) );
	}

	/**
	 * // 存在しないユーザーでないかチェック。
	 *
	 * @param  void
	 * @return bool
	 */
	public function isExistSummoner()
	{
		// OPGGに存在するユーザーか？
		preg_match('/SummonerNotFoundLayout/', $this->html, $matches);
		if(!empty($matches))
		{
			\Log::info('OPGGに該当ユーザーなし：$summoner_name = ' . $this->summoner_name);
			return false;
		}
		return true;
	}

	/**
	 * // $this->htmlから前シーズンのtier/rankを取得
	 *
	 * @param  void
	 * @return array                           // [tier,rank]
	 */
	public function extractBeforeSeasonTierRank()
	{
		// PastRankListタグがあるなら、いずれかの過去シーズンのランクデータがある。
		preg_match('/PastRankList">(.+?)<\/ul/s', $this->html, $matches);
		$tier = "UNRANK";
		$rank = "UNRANK";

		if( !empty($matches[1]) )
		{
			$doc    = new \DOMDocument();
			$doc->loadHTML($matches[1]);
			$xml    = $doc->saveXML();
			$xmlObj = simplexml_load_string($xml);
			$arr    = json_decode(json_encode($xmlObj), true);

			$season = LolSeason::findBeforeSeason();
			foreach( $arr["body"]["li"] as $li )
			{
				if( $li["b"] == $season->season )
				{
					$tmp = explode(" ", $li["@attributes"]["title"]);
			
					$tier = mb_strtoupper($tmp[0]);
					// tierがちゃれますならrankはI固定。
					if( $tier == "CHALLENGER" || $tier == "MASTER" )
					{
						$rank = "I";
					}
					else
					{
						switch($tmp[1])
						{
							case "1":
									$rank = "I";
									break;
							case "2":
									$rank = "II";
									break;
							case "3":
									$rank = "III";
									break;
							case "4":
									$rank = "IV";
									break;
							case "5":
									$rank = "V";
									break;
						}
					}
					break;
				}
			}
		}
		return [
			'tier' => $tier,
			'rank' => $rank,
		];
	}


}
