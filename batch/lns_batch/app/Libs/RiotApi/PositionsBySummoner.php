<?php

namespace App\Libs\RiotApi;

/**
 * // サモナーのランク情報をサモナーIDで引っ張ってくる
 *    $api = new PositionsBySummoner(['summonerId'=>0]);
 */
class PositionsBySummoner extends ApiBase
{
	protected static $path = 'league/v3/positions/by-summoner/%d';
	protected static $dto  = [
		'rank'              => "",
		'tier'              => "",
		'leagueName'        => "",
		'queueType'         => "",
		'playerOrTeamId'    => 0,
		'playerOrTeamName'  => "",
		'leaguePoints'      => 0,
		'wins'              => 0,
		'losses'            => 0,
		'veteran'           => false,
		'inactive'          => false,
		'freshBlood'        => false,
		'hotStreak'         => false,
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
