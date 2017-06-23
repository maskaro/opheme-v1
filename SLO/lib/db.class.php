<?php

class db {
	
	private $db_cred;
	protected $db;
	
	function __construct($_db_cred) {
		
		$this->db_cred = array(
			'host' => $_db_cred['host'],
			'user' => $_db_cred['username'],
			'pass' => $_db_cred['password'],
			'db' => $_db_cred['dbname']
		);
		
		$this->connect();
		
	}
	
	function __destruct() {
		
		$this->disconnect();
		
	}
	
	private function connect() {
		
		$options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET CHARACTER SET utf8', PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');

		try {
			$this->db = new PDO("mysql:host={" . $this->db_cred['host'] . "};dbname={" . $this->db_cred['db'] . "};charset=utf8", $this->db_cred['user'], $this->db_cred['pass'], $options);
		} catch(PDOException $ex) {
			die("Failed to connect to the database: " . $ex->getMessage());
		}
		
		$this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		
	}
	
	private function disconnect() {
		
		$this->db = null;
		
	}
	
	protected function error_message($ex) {
		
		die("Failed to run query! Reason <b>" . $ex->getMessage() . '</b>. Please contact webmaster@opheme.com if this error persists.');
		
	}
	
}