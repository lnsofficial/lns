<?php

Class Db{
	protected $db;
	
	public function __construct(){
		$this->initDb();
	}
	
	public function initDb(){
		$this->db	= new mysqli('localhost', DB_USER, DB_PASSWORD, DB_NAME);
		$this->db->autocommit(False);
		
		if( $this->db->connect_error ){
			echo $this->db->connect_error;
			exit();
		}
	}
	
	public function execute( $sSql ){
		return $this->db->query($sSql);
	}
}

