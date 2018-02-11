<?php

require_once( PATH_RIOTAPI  . 'ApiBase.php' );
require_once( PATH_MODEL    . 'ApiSettings.php' );

/**
 * // トーナメントコードの発行
 *    $api = new SummonersByName();
 */
class SummonersByName extends ApiBase{
    protected static $api_base      = "https://jp1.api.riotgames.com/";
    protected static $path          = "lol/summoner/v3/summoners/by-name/%s";
    protected static $method        = "GET";
    protected        $rate_limit    = true;
    protected static $dto           = [""];
    
    protected function makeUrl(){
        return sprintf(static::$api_base . static::$path, rawurlencode($this->params['name']));
    }
    
    protected function getDefaultResult(){
        return static::$dto;
    }
}
