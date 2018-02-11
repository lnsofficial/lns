<?php

require_once( PATH_RIOTAPI  . 'ApiBase.php' );
require_once( PATH_MODEL    . 'ApiSettings.php' );

/**
 * // トーナメントコードの発行
 *    $api = new PublishTournamentCode();
 */
class PublishTournamentCode extends ApiBase{
    protected static $api_base      = "https://americas.api.riotgames.com/";
    protected static $path          = "lol/tournament/v3/codes?tournamentId=%d";
    protected static $method        = "POST";
    protected        $rate_limit    = false;
    protected static $dto           = [""];
    
    protected function makeUrl(){
        $tournamentId = ApiSettings::getSettingValue("tournamentId");
        
        return sprintf(static::$api_base . static::$path, $tournamentId);
    }
    
    protected function getDefaultResult(){
        return static::$dto;
    }
}
