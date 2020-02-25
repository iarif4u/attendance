<?php
	if(basename($_SERVER['PHP_SELF']) == basename(__FILE__)){
		die("<center><h3>Forbidden...</h3><h4>Sorry, Direct access not allowed<h4></center>");
	}
	class MyDBConnection{
		private $connect;
		private $host;
		private $db_name;
		private $user_name;
		private $user_password;
				
		function connection($host,$dbname,$user,$password){
				$this->host          = $host;
				$this->db_name       = $dbname;
				$this->user_name     = $user;
				$this->user_password = $password;
			try{
				$this->connect = new PDO("mysql:host=$this->host;dbname=$this->db_name",$this->user_name,$this->user_password);
				$this->connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				$this->connect->query('SET NAMES utf8');
				return $this->connect;
			} 
			catch (PDOException $e) {
				die('<center><h3>Connection failed: ' . $e->getMessage()."</center></h3>");
			}
			
		}
	}
?>