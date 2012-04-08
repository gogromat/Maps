<?php 

	class Database {

		private static $dbh;
		
		private function __construct() {}
		
		private static function connect() {

			if(!isset(self::$dbh)) {
				try {
					self::$dbh = new PDO(DB_DSN, DB_USER, DB_PASSWORD, array(ATTR_PERSISTENT => true));
				}
				catch(PDOException $e) {
					self::close();
					trigger_error($e->getMessage(), E_USER_ERROR);
				}
			}
			
			return self::$dbh;
		} 
		
		public static function close() {
			self::$dbh = null;
		}
		
		public static function execute($sql) {
			try {
				$handle = self::connect();
				$stmt = $handle->prepare($sql);
				$stmt->execute();
			}
			catch(PDOException $e) {
				self::close();
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
		}
		
		public static function getAll($sql, $style = PDO::FETCH_ASSOC) {
			$result = null;
			
			try {
				$handle = self::connect();
				$stmt = $handle->prepare($sql);
				$stmt->execute();
				$result = $stmt->fetchAll($style);
			}
			catch(PDOException $e) {
				self::close();
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
			
			return $result;
		}
		
		public static function getRow($sql, $style = PDO::FETCH_ASSOC) {
			$result = null;
			
			try {
				$handler = self::connect();
				$stmt = $handler->prepare($sql);
				$stmt->execute();
				$result = $stmt->fetch($style);
			}
			catch(PDOException $e) {
				self::close();
				trigger_error($e->getMessage(), E_USER_ERROR);
			}
			
			return $result;
		}
	}
?>