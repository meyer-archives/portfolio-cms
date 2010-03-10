<?php

// Incredibly simple template class
class Template {
	private static $data = array();
	private static $template_name;

	public function __construct( $name, $render = false ){
		$portfolio = Portfolio::get_instance();

		if( file_exists( TEMPLATE_PATH . $name.".php" ) ){
			self::$template_name = TEMPLATE_PATH . $name.".php";
		} else {
			die( "Problem loading <strong>{$name}.php</strong>" );
		}

		$this->set("body_id",$name);

		// This will come from variables eventually
		self::set("sitename",SITE_NAME);
		self::set("page_title",false);

		if( $render )
			self::render();
	}

	public static function set($k, $v){
		self::$data[$k] = $v;
	}

	public static function render(){
		extract( self::$data );
		die( "<pre>".print_r( self::$data, 1 ) );
		include_once( TEMPLATE_PATH . "snippets/header.php" );
		echo "\n\n";
		include_once( self::$template_name );
		echo "\n\n";
		include_once( TEMPLATE_PATH . "snippets/footer.php" );
		exit();
	}
}


?>