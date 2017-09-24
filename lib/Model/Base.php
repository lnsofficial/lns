<?php
require_once( PATH_LIB . '/common/Db.php');

class Base{
	const MAIN_TABLE	= "";
	const COL_ID		= "";
	
	// カラム情報
	const DATA	= [
	];
	
	protected $data = [];
	
	protected $db;
	
	function __construct( $oDb, $id = 0 ){
		$this->db = $oDb;
		if( $id ){
			// ID指定があれば取得
			foreach( static::DATA as $key => $value ){
				$sSelectColumn[] = $key;
			}
			$sSelectSql = "SELECT " . implode( ",", $sSelectColumn ) . " FROM " . static::MAIN_TABLE . " WHERE " . static::COL_ID . " = ?";
			$ahsParameter = [ $id ];
			
			$oResult = $this->db->executePrepare( $sSelectSql, "i", $ahsParameter );
			
			while( $row = $oResult->fetch_array() ){
				foreach( static::DATA as $key => $value ){
					$this->$key = $row[$key];
				}
				break;
			}
		} else {
			// ID指定がなければ新規扱い
			foreach( static::DATA as $key => $value ){
				$this->$key = null;
			}
		}
	}
	
	public static function getList( $oDb, $ahsParameter, $ahsOrder = null ){
		$sSelectSql = "SELECT * FROM " . static::MAIN_TABLE . " WHERE ";
		$asParameter = [];
		$sType = "";
		$asWhereSql = [];
		
		foreach( $ahsParameter as $value ){
			if( $value["value"] === null ){
				break;
			}
			switch( $value["type"] ){
				case "int":
					$asWhereSql[] = $value["column"] . " = ? ";
					$asParameter[] = $value["value"];
					$sType .= "i";
					break;
				case "varchar":
					$asWhereSql[] = $value["column"] . " = ? ";
					$asParameter[] = $value["value"];
					$sType .= "s";
					break;
				case "date":
					$asWhereSql[] = $value["column"] . " " . $value["operator"] . " ? ";
					$asParameter[] = $value["value"];
					$sType .= "s";
					break;
			}
		}
		
		//$sSelectSql .= implode( " AND ", $asWhereSql );
		
		$asOrderSql = [];
		if( isset( $ahsOrder ) ){
		    foreach( $ahsOrder as $order ){
		        $asOrderSql[] = $order["column"] . " " . $order["sort_order"];
		    }
		}
		
		$sSelectSql .= implode( " AND ", $asWhereSql );
		if( count( $asOrderSql ) > 0 ){
		    $sSelectSql .= " ORDER BY " . implode( " ,", $asOrderSql );
		}
		
		$oResult = $oDb->executePrepare( $sSelectSql, $sType, $asParameter );
		
		$list = [];
		while( $res = $oResult->fetch_assoc() ){
			$list[] = $res;
		}
		
		return $list;
	}

	public function __get( $key ){
		return $this->get( $key );
	}

	public function __set( $key, $value ){
		$this->set($key,$value);
	}

	public function get( $key, $default = null ){
		if(array_key_exists($key,$this->data)){
			return $this->data[$key];
		}
		return $default;
	}

	public function set( $key, $value ){
		$this->data[$key] = $value;
	}
	
	public function save(){
		$id = static::COL_ID;
		return $this->$id ? $this->update() : $this->insert();
	}
	
	public function update(){
		foreach( static::DATA as $key => $value ){
			switch($value["type"]){
				case "int":
					if( $this->$key === null ) continue;
					$sColumn[] = $key . " = " . $this->$key;
					break;
				case "date":
				case "varchar":
					if( $this->$key === null ) continue;
					$sColumn[] = $key . " = '" . $this->$key . "'";
					break;
			}
		}
		// IDは更新しないので消す
		array_shift($sColumn);
		
		$sUpdateSql = "UPDATE " . static::MAIN_TABLE . " SET " . implode( ",", $sColumn ) . " WHERE " . static::COL_ID . " = ?";
		
		$id = static::COL_ID;
		$ahsParameter = [ $this->$id ];
		
		$oResult = $this->db->executePrepare( $sUpdateSql, "i", $ahsParameter );
		
		return $oResult;
	}
	
	public function insert(){
		foreach( static::DATA as $key => $value ){
			switch($value["type"]){
				case "int":
					if( $this->$key === null ) continue;
					$sColumn[] = $key;
					$sValue[] = $this->$key;
					break;
				case "date":
				case "varchar":
					if( $this->$key === null ) continue;
					$sColumn[] = $key;
					$sValue[] = "'" . $this->$key . "'";
					break;
			}
		}
		
		$sInsertSql = "INSERT INTO " . static::MAIN_TABLE . "(" . implode( ",", $sColumn ) . ") VALUES(" . implode( ",", $sValue ) . ")";
		
		$oResult = $this->db->execute( $sInsertSql );
		$pk = static::COL_ID;
		$this->$pk = $this->db->getLastInsertId();
		return $oResult;
	}
}