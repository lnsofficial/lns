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
use App\Libs\OpggWebpage;
use App\Models\UserRank;
use App\Models\LolSeason;

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

		// OPGGからsummoner_nameを元に前シーズンのランク情報データひっぱってくる
		$opgg     = new OpggWebpage( $user->summoner_name );
		// "サモナーが存在しません"が帰ってきたらエラー
		if( !$opgg->isExistSummoner() )
		{
			// キューを失敗にして次へ。
			$this->log('失敗。OPGGでデータ見つからなかった系。$user->summoner_name:'.$user->summoner_name);
			$queue->result = '失敗。OPGGでデータ見つからなかった系。$user->summoner_name:'.$user->summoner_name;
			$queue->state  = ApiQueue::STATE_FAILED;
			$queue->save();
			return false;
		}
		// それ以外で前シーズンrankが取れなかった場合はUNRANK
		$opgg_tierrank = $opgg->extractBeforeSeasonTierRank();


		// ちゃんと取れたので更新
		try
		{
			LnsDB::beginTransaction();

			//////////////////////////
			// ランク情報を設定する
			//////////////////////////
			$user_rank_now    = UserRank::findByUserId( $user->id );
			$user_rank_before = UserRank::findBeforeSeasonByUserId( $user->id );

			// 現在ランク
			// Unrankの場合は空配列で帰ってくる。
			$tier = 'UNRANK';
			$rank = 'UNRANK';
			if( !empty($sr_json) )
			{
				// SoloQ/FlexQ両方帰ってきてるので、SoloQランクを参照する。
				foreach( $sr_json as $record )
				{
					if( $record['queueType'] == 'RANKED_SOLO_5x5')
					{
						$tier = $record['tier'];
						$rank = $record['rank'];
						break;
					}
					// FlexQしか返ってきてなかったらUNRANK扱いで。
				}
			}
			$user_rank_now->tier    = $tier;
			$user_rank_now->rank    = $rank;
			$user_rank_now->save();

			// 前シーズンランク
			$user_rank_before->tier    = $opgg_tierrank['tier'];
			$user_rank_before->rank    = $opgg_tierrank['rank'];
			$user_rank_before->save();


			//////////////////////////
			// ユーザー情報を設定する
			//////////////////////////
			$from = $user->toArray();
			// サモナー情報更新して、
			$user->summoner_name = $sm_json['name'];
			$user->summoner_id   = $sm_json['id'];
			$user->account_id    = $sm_json['accountId'];
			$user->puuid         = $sm_json['puuid'];
			$user->save();
			$dest = $user->toArray();


			//////////////////////////
			// キューを完了にする
			//////////////////////////
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
