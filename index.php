<?php

include_once( "load.php" );

// Manually disable magic quotes: http://bit.ly/86XywY
if( get_magic_quotes_gpc() ){
	$process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	while( list($key, $val) = each($process) ){
		foreach( $val as $k => $v ){
			unset($process[$key][$k]);
			if( is_array($v) ){
				$process[$key][stripslashes($k)] = $v;
				$process[] = &$process[$key][stripslashes($k)];
			} else {
				$process[$key][stripslashes($k)] = stripslashes($v);
			}
		}
	}
	unset($process);
}

// Load third-party classes and functions
include_once( INCLUDES_PATH . "functions.typogrify.php" );
include_once( INCLUDES_PATH . "wideimage/WideImage.php" );

// Load the functions
include_once( INCLUDES_PATH . "functions.php" );

// Load the classes
include_once( INCLUDES_PATH . "class.base.php" );
include_once( INCLUDES_PATH . "class.router.php" );
include_once( INCLUDES_PATH . "class.db.php" );
include_once( INCLUDES_PATH . "class.portfolio.php" );
include_once( INCLUDES_PATH . "class.template.php" );
include_once( INCLUDES_PATH . "class.cache.php" );

// Route the request
$router = Router::get_instance();

?>