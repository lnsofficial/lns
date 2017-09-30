<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\ApiQueue;
use App\Libs\UtilTime;

// actionの振り分け先増えて新しいバッチ追加するときはここにも追加忘れずに～。
use App\Batches\UpdateUser;
use App\Batches\RegisterUser;

class ExecApiQueue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'batch:api_queue {action}'; // ←優先度指定とかもあっていいかも～。

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'api_queuesの処理';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $action = $this->argument('action');

        // とりあえずテスト
//		$name = $this->ask('What is your name?');

//		$this->info    ('$name         = '.$name);
		$this->line    ('$action       = '.$action);


		// $actionで振り分け
		switch( $action )
		{
			case ApiQueue::ACTION_UPDATE_SUMMONER:
				$batch = new UpdateUser();
				break;

			case ApiQueue::ACTION_REGISTER_SUMMONER:
				$batch = new RegisterUser();
				break;

			default:
				$this->error('指定actionの振り先が未定義');
				return false;
				break;
		}

		try
		{
			$batch->init($this);
//throw new \Exception('Test throw Exception');
			$batch->main();
		}
		catch( \Exception $e )
		{
			// 処理中でException投げた場合はend()通らないない=lockファイル削除されないので、次回実行の時はlockファイルを手動で削除してからで！
			\Log::error($e->getMessage());
//			$batch->postToChatwork('エラー終了: $e->getMessage() = ' . $e->getMessage());
			throw $e;
		}
		$batch->end();
    }



	function log($message)
	{
		$msg = '[' . UtilTime::now() . '] ' . $message;
		$this->line($msg);
	}

}
