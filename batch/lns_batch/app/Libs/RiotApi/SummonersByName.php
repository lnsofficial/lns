<?php

namespace App\Libs\RiotApi;

/**
 * // サモナー情報をサモナー名で引っ張ってくる
 *    $api = new SummonersByName(['name'=>'']);
 */
class SummonersByName extends ApiBase
{
	protected static $path = 'summoner/v3/summoners/by-name/%s';
	protected static $dto  = [
		'profileIconId' => 0,
		'name'          => "",
		'summonerLevel' => 0,
		'revisionDate'  => 0,
		'id'            => 0,
		'accountId'     => 0,
	];
	protected function makeUrl()
	{
		return sprintf(static::$api_base . static::$path, rawurlencode($this->params['name']));
	}
}
