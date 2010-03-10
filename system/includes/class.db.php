<?php

class DB{
	private static $pdo;
	public static function &get_handle() {
		if( self::$pdo === null ){
			try {
				self::$pdo = new PDO('sqlite:'.SITE_PATH.'storage/'.DB_FILENAME);
			} catch( PDOException $e ){ 
				die( "PDO Error: " . $e->getMessage() ); 
			}
		}
		return self::$pdo;
	}
}

?>