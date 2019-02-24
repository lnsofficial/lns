<?php

namespace App\Libs\RiotApi;

/**
 * // サモナーのランク情報をサモナーIDで引っ張ってくる
 *    $api = new PositionsBySummoner(['summonerId'=>0]);
 */
class PositionsBySummoner extends ApiBase
{
	protected static $path = 'league/v4/positions/by-summoner/%s';
	protected static $dto  = [
		'tier'              => "",
		'summonerName'      => "",
		'hotStreak'         => false,
		'wins'              => 0,
		'veteran'           => false,
		'losses'            => 0,
		'rank'              => "",
		'leagueName'        => "",
		'inactive'          => false,
		'freshBlood'        => false,
		'position'          => false,
		'leagueId'          => false,
		'queueType'         => "",
		'summonerId'        => "",
		'leaguePoints'      => 0,
	];
	protected function makeUrl()
	{
		return sprintf(static::$api_base . static::$path, $this->params['summonerId']);
	}
	protected function getDefaultResult()
	{
		return static::$dto;
	}
}
