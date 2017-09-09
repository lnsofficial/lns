<?php

namespace App\Batches;

use \Exception as Exception;
use App\Libs\RiotApi\Summoners;
use App\Libs\RiotApi\LeaguesBySummoner;
use App\Models\ApiQueue;
use App\Models\Member;
use App\Models\LnsDB;

/**
 * // テストバッチ
 * ユーザー情報(m_member→usersになる予定ぽい)について最新のサモナー情報に変える感じのやつを。
 * とりあえずusersがまだないのでm_memberで。

・処理予定のキューの確認
・キューをSTATE_DOINGにマーク
・(処理終わったら)キューをSTATE_FINISHEDにマーク
ここまでいろいろなやつで共通になるはずだから切り出しといて、
・payloadのチェック
・キュー1件の処理ロジック
この２こをabstractで定義しておく感じの基底クラスに変えたい

 */
class BatchUserTier extends BatchBase
{

	public function main()
	{
		$sm_api     = new Summoners();

		// api_queuesテーブルからaction=1,state=0のものを処理する
		$queues = ApiQueue::where  ('action',     ApiQueue::ACTION_UPDATE_SUMMONER)
						  ->where  ('state',      ApiQueue::STATE_UNTREATED)
						  ->orderBy('priority',   'desc')
						  ->orderBy('created_at', 'asc')
						  ->get();

		\Log::debug('処理予定のqueue件数：'.$queues->count());
		$this->log( '処理予定のqueue件数：'.$queues->count() );
		foreach( $queues as $queue )
		{
			// 処理中としてマークつける
			$record = false;
			LnsDB::transaction(function()use($queue, &$record)
			{
				$record = ApiQueue::where ('id',    $queue->id)
								  ->where ('state', ApiQueue::STATE_UNTREATED)
								  ->first();
				if( !empty($record) )
				{
					$record->state = ApiQueue::STATE_DOING;
					$record->save();
				}
			});
			// マークつけれなかったなら次へ。
			if( empty($record) )
			{
				\Log::debug('$id = '.$queue->id.' is not UNTREATED. go to next.');
				$this->log('$id = '.$queue->id.' is not UNTREATED. go to next.');
				continue;
			}
			\Log::debug('$record = '.print_r($record->toArray(),true));


			// payloadの中にちゃんとデータ設定されてるか？
			$payload = json_decode($record->payload, true);
			if( !$this->checkPayload($payload) )
			{
				// キューを失敗にしておく？
				$this->log('失敗。payloadにデータがちゃんと設定されてない。payload:'.$record->payload);
				$queue->result = '失敗。payloadにデータがちゃんと設定されてない。payload:'.$record->payload;
				$queue->state  = ApiQueue::STATE_FAILED;
				$queue->save();
				continue;
			}
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
				// キューを失敗にしておく？
				$this->log('失敗。memberレコードにsummoner_idが設定されてない。$member:'.$member->toJson());
				$queue->result = '失敗。memberレコードにsummoner_idが設定されてない。$member:'.$member->toJson();
				$queue->state  = ApiQueue::STATE_FAILED;
				$queue->save();
				continue;
			}

			// RiotApiからsummoner_idを元にデータひっぱってくる
			$sm_api->setParams(['id'=>$member->summoner_id]);
			$json = $sm_api->execApi();

			// 取れなかったら失敗ということで。
			if( !$sm_api->isSuccess() )
			{
				// キューを失敗にしておく？
				$this->log('失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($json, JSON_UNESCAPED_UNICODE));
				$queue->result = '失敗。RiotApiでデータ見つからなかった系。$json:'.json_encode($json, JSON_UNESCAPED_UNICODE);
				$queue->state  = ApiQueue::STATE_FAILED;
				$queue->save();
				continue;
			}
			\Log::debug('$json = '.print_r($json,true));

			// ちゃんと取れたので、、、
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
	}


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
