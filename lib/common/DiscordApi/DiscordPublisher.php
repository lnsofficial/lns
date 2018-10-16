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
        'LIVE'  => 'https://discordapp.com/api/webhooks/433283524003364864/LlCYwp3R2cH7K2iH4yuCeGZUmLtfDfJgDjhVYK0Qw3sSHb4yekxluSuMj2Zlvg7FsRN-',
        'DEV'   => 'https://discordapp.com/api/webhooks/500472420130357268/97KyqsSkxuCWppRfHuNYGMvS-_hmNGJsrE6W5uVkV-rNNdoTolleAdADpC1dyfr_HbBC',
        'LOCAL' => 'https://discordapp.com/api/webhooks/500473506216280064/gHezUYKBZbyMDr8zqMsZpRFUQ4gzyPrthsbIM8bQOBgv2k08sI2kxODjjvgxU266HwpK',
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
        "*Your will, my hands.*",
        "*Master yourself, master the enemy.*",
        "*Be Cool!!!!!*",
    ];
    const ORIANNA_TIPS = [
        "*We will kill your enemies. That will be fun*",
        "*I have sharp things.*",
        "*We are as one.*",
        "*Yes, I am a weapon.*",
        "*The Ball is angry.*",
        "*So strange, they scream.*",
        "*Why do they keep breaking?*",
        "*Winding.*",
        "*I hear soft things.*",
        "*Motivating.*",
        "*We go.*",
        "*This is very exciting.*",
        "*This is a fun game.*",
        "*The Ball is impatient.*",
        "*Time tick-ticks away.*",
        "*Why are they running?*",
        "*Ravage.*",
        "*Pulse.*",
        "*Protect.*",
        "*Throw*",
        "*I know what makes them tick. I know how to make the ticking stop.*",
        "*They come apart so easily. How do I put them back together again?*",
        "*When you fall, you get right back up.*",
    ];




    /**
     * 試合募集があるよ～、を参加者用Discordに通知する
     * 
     * @param  Match                   $match  // Matchインスタンス
     * @return void
     */
    public static function noticeMatchCreated( Match $match )
    {
        $message    = self::getMessageNoticeMatchCreated( $match );
        self::publish( $message );
    }


    /**
     * チームロゴ更新があったときに、運営Discordに通知する
     * 
     * @param  Teams                   $team  // Teamsインスタンス
     * @return void
     */
    public static function noticeTeamLogoUpdated( Teams $team )
    {
        $message    = self::getMessageNoticeTeamLogoUpdated( $team );

        // local
        //$url = "https://discordapp.com/api/webhooks/425688670671077396/BWQDNG8S644CjptXAwR2zlE_D10yFmdqHCYCxTYVDJ0GFOV3jWzPGgJ4FuuZJJVedIta";
        // live
        $url = "https://discordapp.com/api/webhooks/497828016211361812/DEDpmFMERciF7_sPmC3BfPWzMI1uEuRvK54MQscGGDn0CwXC5OpMpJ8Dk9ikYlwdk4Z1";
        self::publish( $message, $url );
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
        $message .= "さぁ、新しい試合募集だ・・・". "\n\n";


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
        $message .= "配信可否：配信を希望" . ($match->stream ? "する" : "しない") . "\n";
        $message .= "決着形式：BO1\n\n";

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
     * Discordに飛ばすメッセージの作成 [チームロゴ更新されたよ～]
     * 
     * @param  Teams                   $team  // Teamsインスタンス
     * @return string
     */
    private static function getMessageNoticeTeamLogoUpdated( Teams $team )
    {
        $message  = "@here" . "\n";
        $message .= "チームロゴが更新されました。". "\n\n";

        $message .= self::ORIANNA_TIPS[ mt_rand(0, count(self::ORIANNA_TIPS)-1) ] . "\n\n";

        $message .= "チームID：**" . $team->id        . "**\n"; // **で囲むと太字
        $message .= "チーム名：**" . $team->team_name . "**\n"; // **で囲むと太字
        $message .= "タグ名  ：**" . $team->team_tag  . "**\n"; // **で囲むと太字

        return $message;
    }


    /**
     * 飛ばす処理
     * 
     * @param  string                  $message  // Discordに流すメッセージ
     * @param  string                  $url      // 通知先URL指定する場合
     * @return void
     */
    private static function publish( $message = "", $url = "" )
    {
        // 環境で通知先切り替えるかんじ
        $params = [
            'url'  => empty($url) ? self::getWebhookUrl() : $url,
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
