<?php

// Template Class
class Template {
	private static $data = array();
	private static $template_name;
	private static $format;

	public function __construct( $name = false ){
		$p = Portfolio::get_instance();
//		$r = Router::get_instance();

		self::$format = "html";

		self::set( 'items_by_id', $p->items_by_id() );
		self::set( 'items_by_project', $p->items_by_project() );
		self::set( 'projects_by_id', $p->projects_by_id() );
		self::set( 'projects_by_slug', $p->projects_by_slug() );

		self::set( 'MEDIA_URL', MEDIA_URL );
		self::set( 'SYS_MEDIA_URL', SYS_MEDIA_URL );

		if( file_exists( TEMPLATE_PATH . $name . "." . self::$format ) ){
			self::$template_name = $name;
			self::set( "body_id", $name."-page");
			self::set( "page_title", false );
		} else {
			self::$template_name = "error";
			self::set( "status_code", 404 );
			self::set( "body_id", "error-page" );
			self::set( "error_message", "Page not found" );
			self::set( "error_details", "The page you are looking for could not be found." );
			self::set( "page_title", "Page not found" );
			self::render();
		}
	}

	public static function set($k, $v){
		self::$data[$k] = $v;
	}

	public static function render(){
		if( !empty( self::$data['status_code'] ) ){
			switch( self::$data['status_code'] ){
				case 404:
				break;
			}
		}

		$h2o = new h2o(
			self::$template_name . "." . self::$format,
			array(
				"searchpath" => TEMPLATE_PATH,
				"cache" => false, // default "file"
				"cache_dir" => SITE_PATH . "storage/cache/"
			)
		);
		echo $h2o->render(self::$data);
		exit();
	}
}


?>