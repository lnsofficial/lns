<?php

namespace App\Batches;

use \Exception as Exception;
use App\Models\ApiQueue;
use App\Models\LnsDB;
// ↑たぶん不変

// ↓バッチによって可変
use App\Libs\RiotApi\Summoners;
use App\Models\Member;

/**
 * // ユーザー情報(m_member→usersになる予定ぽい)について最新のサモナー情報に変える感じのやつを。
 * とりあえずusersがまだないのでm_memberで。
 */
class UpdateUser extends QueueBase
{

	public function queue_action( $queue )
	{
		// payload切り出しておく。
		$payload = json_decode($queue->payload, true);

		// 該当のm_memberレコード取ってくる
		$member  = Member::find($payload['member_id']);
		if( empty($member) )
		{
			// キューを失敗にしておく？
			$this->log('失敗。memberテーブルに該当レコード見当たらず。member_id:'.$payload['member_id']);
			$queue->result = '失敗。memberテーブルに該当レコード見当たらず。member_id:'.$payload['member_id'];
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			continue;
		}
		// 該当のm_memberレコードにsummoner_idが設定されてなかったらだめ！
		if( empty($member->summoner_id) )
		{
			// キューを失敗にして次へ。
			$this->log('失敗。memberレコードにsummoner_idが設定されてない。$member:'.$member->toJson());
			$queue->result = '失敗。memberレコードにsummoner_idが設定されてない。$member:'.$member->toJson();
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			continue;
		}

		// RiotApiからsummoner_idを元にデータひっぱってくる
		$sm_api     = new Summoners();
		$sm_api->setParams(['id'=>$member->summoner_id]);
		$json = $sm_api->execApi();

		// 取れなかったら失敗ということで。
		if( !$sm_api->isSuccess() )
		{
			// キューを失敗にして次へ。
			$this->log('失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($json, JSON_UNESCAPED_UNICODE));
			$queue->result = '失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($json, JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			continue;
		}
		\Log::debug('$json = '.print_r($json,true));


		// ちゃんと取れたので更新
		LnsDB::transaction(function()use(&$member, &$queue, $json)
		{
			$from = $member->toArray();
			// サモナー情報更新して、
			$member->summoner_name = $json['name'];
			$member->account_id    = $json['accountId'];
			$member->save();
			$dest = $member->toArray();
			// キューを完了にする
			$this->log('$id = '.$queue->id.' is Finished. go to next.');
			$queue->result = json_encode(['from'=>$from,'desc'=>$dest], JSON_UNESCAPED_UNICODE);
			$queue->state  = ApiQueue::STATE_FINISHED;
			$queue->save();
		});

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
		if( empty($payload) || empty($payload['member_id']) )
		{
			$res = false;
		}
		return $res;
	}

}
