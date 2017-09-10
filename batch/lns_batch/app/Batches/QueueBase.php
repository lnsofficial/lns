<?php

namespace App\Batches;

use \Exception as Exception;
use App\Models\ApiQueue;
use App\Models\LnsDB;

/**
 * // キューを順次処理していくための、共通的なやつを定義した抽象クラス

・処理予定のキューの確認
・キューをSTATE_DOINGにマーク
ここまでを担当。

・payloadのチェック
・キュー1件の処理ロジック
・(処理終わったら)キューをSTATE_FINISHEDにマーク
この３こは継承先でやってね、という感じ。
 */
abstract class QueueBase extends BatchBase
{
	protected static $action     = 0;                                            // ★継承先で定義すること。

	public function main()
	{
		// api_queuesテーブルからaction=1,state=0のものを処理する
		$queues = ApiQueue::where  ('action',     static::$action)
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
			LnsDB::transaction(function()use(&$queue)
			{
				$queue  = ApiQueue::where ('id',    $queue->id)
								  ->where ('state', ApiQueue::STATE_UNTREATED)
								  ->first();
				if( !empty($queue) )
				{
					$queue->state = ApiQueue::STATE_DOING;
					$queue->save();
				}
			});
			// マークつけれなかったなら次へ。
			if( empty($queue) )
			{
				\Log::debug('$id = '.$queue->id.' is not UNTREATED. go to next.');
				$this->log('$id = '.$queue->id.' is not UNTREATED. go to next.');
				continue;
			}
			\Log::debug('$queue = '.print_r($queue->toArray(),true));


			// payloadの中にちゃんとデータ設定されてるか？
			$payload = json_decode($queue->payload, true);

			// ★↓継承先で処理内容定義してほしい！
			if( !$this->checkPayload($payload) )
			{
				// キューを失敗にしておく？
				$this->log('失敗。payloadにデータがちゃんと設定されてない。payload:'.$queue->payload);
				$queue->result = '失敗。payloadにデータがちゃんと設定されてない。payload:'.$queue->payload;
				$queue->state  = ApiQueue::STATE_FAILED;
				$queue->save();
				continue;
			}

			// ★↓継承先で処理内容定義してほしい！
			if( !$this->queue_action($queue) )
			{
				continue;
			}
		}
	}



    /**
     * // キュー1件1件の実際の処理内容。
     * 
     * @param  ApiQueue          $queue             // ApiQueueクラスのインスタンス
     * @return void
     */
    abstract protected function queue_action( $queue );


    /**
     * // キュー処理にあたってのpayloadのチェック。actionによって入ってるものが違う想定。
     * 
     * @param  array          $payload             // $payload = json_decode($queue->payload, true);
     * @return bool
     */
    abstract protected function checkPayload( $payload );

}
