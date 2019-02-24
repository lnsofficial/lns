<?php

namespace App\Libs\RiotApi;

/**
 * // サモナー情報をサモナーIDで引っ張ってくる
 *    $api = new Summoners(['id'=>0]);
 */
class Summoners extends ApiBase
{
	protected static $path = 'summoner/v4/summoners/%d';
	protected static $dto  = [
		'profileIconId' => 0,
		'name'          => "",
		'puuid'         => 0,
		'summonerLevel' => 0,
		'accountId'     => "",
		'id'            => "",
		'revisionDate'  => 0,
	];
	protected function makeUrl()
	{
		return sprintf(static::$api_base . static::$path, $this->params['id']);
	}
}
