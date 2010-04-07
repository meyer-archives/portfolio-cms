<?php

class User{
	private static $instance;
 
	public static function &get_instance() {
		if( self::$instance === null )
			self::$instance = new User();
		return self::$instance;
	}

	private function __construct(){
		return;
	}

	private function is($what){
		switch($what){
			default:
			break;

			case "logged_in":
			break;
		}
	}

	public function is_logged_in(){
		return $this->is("logged_in");
	}
}

?>