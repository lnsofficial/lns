<?php

namespace App\Batches;

use \Exception as Exception;
use App\Models\ApiQueue;
use App\Models\LnsDB;
// ↑たぶん不変

// ↓バッチによって可変
use App\Libs\RiotApi\SummonersByName;
use App\Libs\RiotApi\PositionsBySummoner;
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

		// RiotApiからsummoner_nameを元にサモナー情報データひっぱってくる
		$sm_api     = new SummonersByName();
		$sm_api->setParams(['name'=>$user->summoner_name]);
		$sm_json    = $sm_api->execApi();

		// 取れなかったら失敗ということで。
		if( !$sm_api->isSuccess() )
		{
			// キューを失敗にして次へ。
			$this->log('失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($sm_json, JSON_UNESCAPED_UNICODE));
			$queue->result = '失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($sm_json, JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			return false;
		}
		\Log::debug('$json = '.print_r($sm_json,true));

		// RiotApiからsummoner_idを元にランク情報データひっぱってくる
		$sr_api     = new PositionsBySummoner();
		$sr_api->setParams(['summonerId'=>$sm_json['id']]);
		$sr_json    = $sr_api->execApi();

		// 取れなかったら失敗ということで。
		if( !$sr_api->isSuccess() )
		{
			// キューを失敗にして次へ。
			$this->log('失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($sr_json, JSON_UNESCAPED_UNICODE));
			$queue->result = '失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($sr_json, JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			return false;
		}
		\Log::debug('$json = '.print_r($sr_json,true));



		// ちゃんと取れたので更新
		try
		{
			LnsDB::beginTransaction();

			$from = $user->toArray();
			// サモナー情報更新して、
			$user->summoner_name = $sm_json['name'];
			$user->summoner_id   = $sm_json['id'];
			$user->account_id    = $sm_json['accountId'];

			// Unrankの場合は空配列で帰ってくる。
			$rank = 'UNRANK';
			$tier = 'UNRANK';
			if( !empty($sr_json) )
			{
				$rank = $sr_json[0]['rank'];
				$tier = $sr_json[0]['tier'];
			}
			$user->rank    = $rank;
			$user->tier    = $tier;


			$user->save();
			$dest = $user->toArray();
			// キューを完了にする
			$this->log('$id = '.$queue->id.' is Finished. go to next.');
			$queue->result = json_encode(['from'=>$from,'desc'=>$dest], JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FINISHED;
			$queue->save();

			LnsDB::commit();
		}
		catch( Exception $e )
		{
			// DB更新で失敗したならしょうがないので次へ・・・。
			$this->log('DB更新失敗：$e->getMessage() = '.$e->getMessage());
			LnsDB::rollBack();

			// 失敗ステータスにしてpayloadに詰め込んでおく。
			LnsDB::beginTransaction();
			$queue->result = 'DB更新失敗：$e->getMessage() = '.$e->getMessage();
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			LnsDB::commit();
			return false;
		}

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
