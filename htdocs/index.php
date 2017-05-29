<?php
// TODO エラー処理
ini_set('display_errors', 0);

require_once('../lib/common/Define.php');
require_once('../lib/common/Dispatcher.php');

$oDispatcher = new Dispatcher();
$oDispatcher->dispatch();
