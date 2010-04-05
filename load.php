<?php

include( "config.php" );

date_default_timezone_set('America/New_York');

if( get_magic_quotes_gpc() )
	die( "magic_quotes is enabled, but should not be. <a href='http://bit.ly/86XywY'>Fix that problem</a>." );

define( "SITE_PATH", dirname(__FILE__) . "/" );
define( "SITE_URL", "/" );
define( "API_URL", SITE_URL . "api/" );
define( "PROJECT_PREFIX", "gallery/" );

define( "STORAGE_URL", "/storage/" );
define( "STORAGE_PATH", SITE_PATH . "storage/" );

define( "IMAGE_PATH", STORAGE_PATH . "images/" );
define( "IMAGE_URL", STORAGE_URL . "images/" );

define( "SYS_PATH", SITE_PATH . "system/" );

define( "MEDIA_URL", "/media/" );
define( "MEDIA_PATH", SITE_PATH . "media/" );
define( "TEMPLATE_PATH", MEDIA_PATH . "templates/" );

define( "SYS_MEDIA_URL", "/system/media/" );
define( "SYS_MEDIA_PATH", SITE_PATH . "system/media/" );

define( "INCLUDES_PATH", SYS_PATH . "includes/" );

// Load third-party classes and functions
include_once( INCLUDES_PATH . "typogrify/smartypants.php" );
include_once( INCLUDES_PATH . "typogrify/typogrify.php" );
include_once( INCLUDES_PATH . "typogrify/markdown.php" );
include_once( INCLUDES_PATH . "wideimage/WideImage.php" );

// Load the functions
include_once( INCLUDES_PATH . "functions.php" );

// Load the classes
include_once( INCLUDES_PATH . "class.base.php" );
include_once( INCLUDES_PATH . "class.router.php" );
include_once( INCLUDES_PATH . "class.db.php" );
include_once( INCLUDES_PATH . "class.portfolio.php" );
include_once( INCLUDES_PATH . "twig/Autoloader.php" );
include_once( INCLUDES_PATH . "class.template.php" );
include_once( INCLUDES_PATH . "tags.php" );
include_once( INCLUDES_PATH . "class.cache.php" );

// Route the request
$router = Router::get_instance();

?>