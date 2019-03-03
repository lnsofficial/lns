<?php

require_once( PATH_MODEL . 'ApiCallStatus.php' );

abstract class ApiBase{
    protected        $apikey        = '';
    protected static $api_base      = '';                                            // リージョンとかも変えれるようにするとよりいいかも。
    protected static $path          = '';                                            // ★継承先で定義すること。
    protected        $url           = '';
    protected static $dto           = [];                                            // ★継承先で定義すること。
    protected        $params        = [];                                            // ★コンストラクタで渡す もしくは setParams() で設定すること
    protected        $code          = 0;
    protected        $rate_limit    = true;                                          // 内部的なレートリミットの制限、デフォルトTrue
    protected static $method        = '';                                            // APIの実行メソッド
    
    abstract protected function makeUrl();                                      // ★継承先で定義すること。

    public function __construct( $prm=[] ){
        $this->params = $prm;
        $this->apikey = RIOTAPIKEY;
    }
    
    /**
     * // Usage: $testapi  = new SummonersByName();
     *           $testapi->setParams(['name'=>'ygnizer']);
     *
     * @param  array                    $prm                          // 
     * @return void
     */
    public function setParams( $prm=[] ){
        $this->params = $prm;
        $this->url    = $this->makeUrl();
        $this->code   = 0;
    }
    
    /**
     * // RiotApiKeyを設定
     *
     * @param  array                    $apikey                          // RiotApiKey文字列
     * @return void
     */
    public function setApiKey( $apikey ){
        $this->apikey = $apikey;
    }
    
    public function isSuccess(){
        return $this->code == 200 ? true : false;
    }
    
    /**
     * // Usage: $testapi  = new SummonersByName(['name'=>'ygnizer']);
     *           $json_arr = $testapi->execApi();
     *
     * @return array
     */
    public function execApi(){
        if( empty($this->apikey) || empty(static::$path) || empty($this->params) ){
            return $this->getDefaultResult();
        }
        
        $data = [];
        
        // RateLimitについてこちら側で抑えておく
        if( !$this->checkRateLimit() ){
            return false;
        }
        
        try{
            $data = $this->call_api();
        }catch( Exception $e ){
            // エラー表示
            return false;
        }
        
        if( empty($data) ){
            return false;
        }
        
        $json = json_decode($data['body'], true);
        $this->code = $data['code'];
        if( $data['code'] != '200' ){
            $json = $this->getDefaultResult();
        }
        
        return $json;
    }


    ////////////////////////// ↓↓↓ここから内部関数↓↓↓ //////////////////////////
    protected function checkRateLimit(){
        if( !$this->rate_limit ){
            // レートリミットのチェックがなければ常にOK
            return true;
        }
        
        // statusとってくる
        $oDb = new Db();
        $status = ApiCallStatus::getStatus($oDb, $this->getApiKey());
        $check  = $status->checkLimit();
        
        if( !$check['enable'] ){
            // レートリミット引っかかってたらエラー
            return false;
        }
        
        $oDb->commit();
        
        return true;
    }
    
    protected function getApiKey(){
        return $this->apikey;
    }
    
    protected function getUrl(){
        if( empty($this->url) ){
            $this->url = $this->makeUrl();
        }
        return $this->url;
    }
    
    protected function getDefaultResult(){
        return static::$dto;
    }
    
    protected function call_api(){
        $ch          = $this->setupCurl();
        $response    = curl_exec($ch);
        $code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // ヘッダサイズ取得
        $header      = substr($response, 0, $header_size);      // headerだけ切り出し
        $body        = substr($response, $header_size);         // bodyだけ切り出し
        curl_close($ch);
        var_dump($code);
        var_dump($header);
        var_dump($body);
        
        $data = null;

        switch( $code ){
            case '200':
                // 正常終了
                $data = [
                    'url'          => $this->getUrl(),
                    'params'       => $this->params,

                    'response'     => $response,
                    'code'         => $code,
                    'header'       => $header,
                    'body'         => $body,
                ];
                break;
            case '429': // (Rate Limit Exceeded)
                // レートリミット引っかかったのでエラー
                // 何か特別な処理入れる？
                break;
            default:
                // それ以外は一旦全部エラー
/*
            case '400': // (Bad Request)
            case '404': // (Not Found)
            case '403': // (Forbidden)
            case '415': // (Unsupported Media Type)
            case '500': // (Internal Server Error)
            case '503': // (Service Unavailable)
*/
                break;
        }

        return $data;
    }
    
    private function setupCurl(){
        $url = $this->getUrl();
        
        // curl準備、実行
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,     static::$method);
        if( $this->method = "POST" ){
            curl_setopt($ch, CURLOPT_POSTFIELDS,        json_encode($this->params));
        }
        curl_setopt($ch, CURLOPT_URL,               $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
        curl_setopt($ch, CURLOPT_HEADER,            true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,        ["X-Riot-Token:" . $this->getApiKey()]);

        return $ch;
    }

}
