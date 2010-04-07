<?php

include( "config.php" );

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

define( "INCLUDES_PATH", SYS_PATH . "classes/" );

// Load third-party classes and functions
include_once( INCLUDES_PATH . "typogrify/smartypants.php" );
include_once( INCLUDES_PATH . "typogrify/typogrify.php" );
include_once( INCLUDES_PATH . "typogrify/markdown.php" );
include_once( INCLUDES_PATH . "wideimage/WideImage.php" );

// Load the functions
include_once( SYS_PATH . "functions.php" );

// Load the classes
include_once( INCLUDES_PATH . "base.php" );
include_once( INCLUDES_PATH . "router.php" );
include_once( INCLUDES_PATH . "db.php" );
include_once( INCLUDES_PATH . "portfolio.php" );
include_once( INCLUDES_PATH . "twig/Autoloader.php" );
include_once( INCLUDES_PATH . "template.php" );
include_once( INCLUDES_PATH . "cache.php" );

Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(TEMPLATE_PATH);
$twig = new Twig_Environment( $loader , array(
	'cache' => false,//STORAGE_PATH . "cache/",
	'debug' => true,
	'auto_reload' => true,
	'base_template_class' => 'Template_Extras'
));

// Twig Tags
include_once( SYS_PATH . "tags.php" );

$twig->addExtension(new Twig_Extras());

// Route the request
$router = Router::get_instance();

$router->route();

?>