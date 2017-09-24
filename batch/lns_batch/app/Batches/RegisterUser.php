<?php

namespace App\Batches;

use \Exception as Exception;
use App\Models\ApiQueue;
use App\Models\LnsDB;
// ↑たぶん不変

// ↓バッチによって可変
use App\Libs\RiotApi\SummonersByName;
use App\Models\User;

/**
 * // ユーザー情報(users)について初回登録時にサモネからsummoner_id問い合わせて確定させるみたいなやつ。
 * 
 */
class RegisterUser extends QueueBase
{
	protected static $action     = ApiQueue::ACTION_REGISTER_SUMMONER; // ユーザー初回登録時のriotApiへの問い合わせ

	public function queue_action( $queue )
	{
		// payload切り出しておく。
		$payload = json_decode($queue->payload, true);

		// 該当のusersレコード取ってくる
		$user  = User::find($payload['user_id']);
		if( empty($user) )
		{
			// キューを失敗にしておく？
			$this->log('失敗。userテーブルに該当レコード見当たらず。user_id:'.$payload['user_id']);
			$queue->result = '失敗。usersテーブルに該当レコード見当たらず。user_id:'.$payload['user_id'];
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			return false;
		}

		// RiotApiからsummoner_nameを元にデータひっぱってくる
		$sm_api     = new SummonersByName();
		$sm_api->setParams(['name'=>$user->summoner_name]);
		$json = $sm_api->execApi();

		// 取れなかったら失敗ということで。
		if( !$sm_api->isSuccess() )
		{
			// キューを失敗にして次へ。
			$this->log('失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($json, JSON_UNESCAPED_UNICODE));
			$queue->result = '失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($json, JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			return false;
		}
		\Log::debug('$json = '.print_r($json,true));


		// ちゃんと取れたので更新
		LnsDB::transaction(function()use(&$user, &$queue, $json)
		{
			$from = $user->toArray();
			// サモナー情報更新して、
			$user->summoner_name = $json['name'];
			$user->summoner_id   = $json['id'];
			$user->account_id    = $json['accountId'];
			$user->save();
			$dest = $user->toArray();
			// キューを完了にする
			$this->log('$id = '.$queue->id.' is Finished. go to next.');
			$queue->result = json_encode(['from'=>$from,'desc'=>$dest], JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FINISHED;
			$queue->save();
		});

		return true;
	}


    /**
     * // キュー処理にあたってのpayloadのチェック。actionによって入ってるものが違う想定。
     * 
     * @param  array          $payload             // $payload = json_decode($queue->payload, true);
     * @return bool
     */
	protected function checkPayload( $payload )
	{
		$res = true;
		if( empty($payload) || empty($payload['user_id']) )
		{
			$res = false;
		}
		return $res;
	}

}
