<?php

Class Db{
	protected $db;
	
	public function __construct(){
		$this->initDb();
	}
	
	public function initDb(){
		$this->db	= new mysqli('localhost', DB_USER, DB_PASSWORD, DB_NAME);
		$this->db->autocommit(false);
		
		if( $this->db->connect_error ){
			echo $this->db->connect_error;
			exit();
		}
	}
	
	public function execute( $sSql ){
		return $this->db->query($sSql);
	}
	
	public function executePrepare( $sSql, $sType, $ahsParameter ){
		$stmt = $this->db->prepare( $sSql );
		$sqlParam = array( $sType );
		foreach( $ahsParameter as &$value ){
			$sqlParam[] = &$value;
		}
		
		call_user_func_array( array( $stmt, 'bind_param' ), $sqlParam );
		$stmt->execute();
		
		$oResult = $stmt->get_result();
		
		$stmt->close();
		
		return $oResult;
	}
	
	public function beginTransaction(){
		$this->db->autocommit(false);
		$this->db->begin_transaction();
		return true;
	}
	
	public function commit(){
		$this->db->commit();
		return true;
	}

	public function getLastInsertId(){
		return $this->db->insert_id;
	}
}


class MysqlSessionHandler implements SessionHandlerInterface {
	/**
	 * MySQL接続情報を持つ
	 * @var mysqli
	 */
	private $oDb = null;
	
	public function __construct(){
		session_set_save_handler(
			array( $this, 'open'),
			array( $this, 'close'),
			array( $this, 'read'),
			array( $this, 'write'),
			array( $this, 'destroy'),
			array( $this, 'gc')
		);
	}

	/**
	 * セッションを開始する際に呼び出される。MySQLへの接続を開始する。
	 * @param  string $savePath session.save_pathで設定されているパス
	 * @param  string $saveName session.nameで設定されている名前(PHPSESSID)
	 * @return bool
	 */
	function open($savePath, $saveName){
		$this->oDb = new Db();
		return true;
	}

	/**
	 * セッションを閉じる際に呼び出される。MySLQへの接続を閉じる。
	 * @return bool
	 */
	function close(){
		return true;
	}

	/**
	 * セッションのデータを読み込む。対象のレコードを取り出してデータを返す
	 * @param  string $id セッションID
	 * @return string セッションのデータ
	 */
	function read($id){
		$sSelectSessionSql = "SELECT * " .
							"FROM " .
								" t_session " .
							" WHERE " .
								" id = '" . $id . "'";
		$oSelectSesionResult = $this->oDb->execute($sSelectSessionSql);
		
		if( $oSelectSesionResult->num_rows == 1){
			$row = $oSelectSesionResult->fetch_row();
			return $row[1];
		} else {
			return "";
		}
	}
	
	/**
	 * セッションのデータを書き込む。レコードを追加・更新する
	 * @param  string $id セッションID
	 * @param  string $data セッションのデータ $_SESSIONをシリアライズしたもの
	 * @return bool
	 */
	function write($id, $data){
		$sSelectSessionSql = "SELECT * " .
							"FROM " .
								" t_session " .
							" WHERE " .
								" id = '" . $id . "'";
		$oSelectSesionResult = $this->oDb->execute( $sSelectSessionSql );
		
		if( $oSelectSesionResult->num_rows == 1){
			$sUpdateSessionSql	= "UPDATE " .
									" t_session " .
								" SET " .
									" session = " . $data .
								" WHERE " .
									" id =  '" . $id . "'";
			$oUpdateResult = $this->oDb->execute( $sUpdateSessionSql );
			$this->oDb->commit();
		} else {
			$date = date('Y-m-d H:i:s');
			$sInsertSessionSql	= "INSERT INTO " .
									" t_session(id,session) " .
								"VALUES('" . $id . "','" . $data . "')";
			$oInsertResult = $this->oDb->execute( $sInsertSessionSql );
			$this->oDb->commit();
		}
		return true;
	}

	/**
	 * セッションを破棄する。対象のレコードを削除します。
	 * @param  string $id セッションID
	 * @return bool
	 */
	function destroy($id){
		$sDeleteSessionSql	= "DELETE FROM t_session WHERE id = '" . $id . "'";
		$oResult = $this->oDb->execute($sDeleteSessionSql);
		$this->oDb->commit();
		return true;
	}

	/**
	 * 古いセッションを削除する。古いレコードを削除します。
	 * @param  string $maxlifetime セッションのライフタイム session.gc_maxlifetimeの値
	 * ブラウザ閉じたタイミングでログアウト
	 * @return bool
	 */
	function gc($maxlifetime){
		$maxlifetime = preg_replace('/[^0-9]/', '', $maxlifetime);
		$sDeleteSessionSql = "DELETE FROM t_session WHERE (TIMESTAMP(CURRENT_TIMESTAMP) - TIMESTAMP(create_date)) > ${maxlifetime}" ;
		$oResult = $this->oDb->execute($sDeleteSessionSql);
		$this->oDb->commit();
		return true;
	}
}

function require_unlogined_session(){
	// セッション開始
	@session_start();
	// ログインしていれば募集一覧に遷移
	if (isset($_SESSION['id'])) {
		header('Location: /Match/RecruitList');
		exit;
	}
}

function require_logined_session(){
	// セッション開始
	@session_start();
	// ログインしていなければ /login.php に遷移
	if (!isset($_SESSION['id'])) {
		header('Location: /login.html');
		exit;
	}
}
