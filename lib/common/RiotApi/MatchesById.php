<?php

require_once( PATH_RIOTAPI  . 'ApiBase.php' );
require_once( PATH_MODEL    . 'ApiSettings.php' );

/**
 * // トーナメントコードの発行
 *    $api = new SummonersByName();
 */
class MatchesById extends ApiBase{
    protected static $api_base      = "https://jp1.api.riotgames.com/";
    protected static $path          = "lol/match/v4/matches/%s";
    protected static $method        = "GET";
    protected        $rate_limit    = true;
    protected static $dto           = [""];
    
    protected function makeUrl(){
        return sprintf(static::$api_base . static::$path, rawurlencode($this->params['id']));
    }
    
    protected function getDefaultResult(){
        return static::$dto;
    }
}
