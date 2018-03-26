<?php
/**
 * Discordに通知飛ばす系？のやつ。
 *
 * 基本staticな使い方で。インスタンス作成する意味なさそう。
 *
 * 例：DiscordPublisher::noticeMatchCreated();
 *
 * ※lib/common/env.ini が配置されてること。これみてどこのDiscordのどのチャットに飛ばすか変える感じ。
 */

require_once( PATH_MODEL . 'Match.php' );
require_once( PATH_MODEL . 'Teams.php' );
require_once( PATH_MODEL . 'TeamMembers.php' );

class DiscordPublisher
{
    static $ini;
    const WEBHOOK_URLS     = [
        'LIVE'  => 'https://discordapp.com/api/webhooks/425659894159638538/hlUULvWG48DGmUlleJaDv0nRL6Twe_LcIyiA36o0geYSXhbhdbIEYq2ceyq8_zo8_0cq', // ←ここあとで本番用に変えること。
        'DEV'   => 'https://discordapp.com/api/webhooks/425659894159638538/hlUULvWG48DGmUlleJaDv0nRL6Twe_LcIyiA36o0geYSXhbhdbIEYq2ceyq8_zo8_0cq',
        'LOCAL' => 'https://discordapp.com/api/webhooks/425688670671077396/BWQDNG8S644CjptXAwR2zlE_D10yFmdqHCYCxTYVDJ0GFOV3jWzPGgJ4FuuZJJVedIta',
    ];
    const TIPS = [
        "*Here comes a new Challenger !*",
        "*Wake up, People !*",
        "*Do, or do not. There is no try.*",
        "*Its payback time, Go dance with the angels !*",
        "*Hey, whats all the ruckus outside ?*",
        "*Life is too short to wait.*",
        "*Why not take a shot at that ?*",
        "*Take it easy.*",
        "*Im sure everything is OK.*",
        "*Just do it !*",
        "*Hello, are you having fun ?*",
        "*Now, you are off to your next big challenge！*",
        "*You miss 100% of the shots you dont take.*",
        "*The only one who can beat me is me.*",
    ];




    /**
     * 試合募集があるよ～、をDiscordに通知する
     * 
     * @param  Match                   $match  // Matchインスタンス
     * @return void
     */
    public static function noticeMatchCreated( Match $match )
    {
        $message    = self::getMessageNoticeMatchCreated( $match );
        self::publish( $message );
    }


////////////////////////////////////////// ここからprivate関数 //////////////////////////////////////////

    /**
     * 環境別のWebhookUrlを返す。
     * 
     * @return string
     */
    private static function getWebhookUrl()
    {
        return self::WEBHOOK_URLS[ENV];
    }


    /**
     * Discordに飛ばすメッセージの作成 [試合募集があるよ～]
     * 
     * @param  Match                   $match  // Matchインスタンス
     * @return string
     */
    private static function getMessageNoticeMatchCreated( Match $match )
    {
        // チーム名を取得
        $db = new Db();
        $team = new Teams( $db, $match->host_team_id );
        $hostname = $team->team_name . " (" . $team->team_tag . ")";
        $league = $team->getLeague($db);


        $message  = "@here" . "\n";
        $message .= "新しい試合募集があります！". "\n\n";

        $message .= self::TIPS[ mt_rand(0, count(self::TIPS)-1) ] . "\n\n";

        $message .= "募集チーム：**" . $hostname . "**\n"; // **で囲むと太字
        $message .= "所属ブロック：" . $league->league_name . "\n\n";

        $message .= "募集日時：" . $match->match_date . "\n";

        switch( $match->type )
        {
            case Match::MATCH_TYPE_ANY:
                $msg_tmp = "どこからでも";
                break;
            case Match::MATCH_TYPE_LESS_SAME:
                $msg_tmp = "同ブロック以下";
                break;
            case Match::MATCH_TYPE_LESS_ONE_ON_THE_SAME:
                $msg_tmp = "１つ上のブロック以下";
                break;
            case Match::MATCH_TYPE_LESS_TWO_ON_THE_SAME:
                $msg_tmp = "２つ上のブロック以下";
                break;
            default:
                $msg_tmp = "";
                break;
        }
        $message .= "ブロック指定：" . $msg_tmp . "\n";
        $message .= "配信可否：配信を希望" . ($match->stream ? "する" : "しない") . "\n\n";

        // ランダムなチームメンバーからの一言を付け加えたい。
        $team_members = TeamMembers::getByTeamId( $team->id );
        // comment設定してない人を弾く
        $members = [];
        foreach( $team_members as $team_member )
        {
            if( !empty($team_member['comment']) )
            {
                $members[] = $team_member;
            }
        }
        // ランダムで一人選ぶ
        if( count($members) )
        {
            $member = $members[ mt_rand(0, count($members)-1) ];
            $message .= $member['summoner_name'] . "さんより：「" . $member['comment'] . "」";
        }

        return $message;
    }


    /**
     * 飛ばす処理
     * 
     * @param  string                  $message  // Discordに流すメッセージ
     * @return void
     */
    private static function publish( $message = "" )
    {
        // 環境で通知先切り替えるかんじ
        $params = [
            'url'  => self::getWebhookUrl(),
            'data' => [
                'content' => $message,
            ],
        ];
        $ch          = self::setupCurl($params);
        $response    = curl_exec($ch);
        $code        = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE); // ヘッダサイズ取得
        $header      = substr($response, 0, $header_size);      // headerだけ切り出し
        $body        = substr($response, $header_size);         // bodyだけ切り出し
        curl_close($ch);

        switch( $code )
        {
            case '200':
                // なんかしらログだせるといいかも～。
                break;
            default:
                // なんかしらログだせるといいかも～。
                break;
        }
    }
    private function setupCurl( $params = [] )
    {
        // curl準備、実行
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,     "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS,        json_encode($params['data']));
        curl_setopt($ch, CURLOPT_URL,               $params['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
        curl_setopt($ch, CURLOPT_HEADER,            true);
        return $ch;
    }



}
