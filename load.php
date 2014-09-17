<?php

include( "config.php" );

session_start();

if( get_magic_quotes_gpc() )
	die( "magic_quotes is enabled, but should not be. <a href='http://bit.ly/86XywY'>Fix that problem</a>." );

define( "SITE_PATH", dirname(__FILE__) . "/" );
define( "SITE_URL", "/" );
define( "API_URL", SITE_URL . "api/" );

define( "STORAGE_PATH", SITE_PATH . "storage/" );
define( "STORAGE_URL", SITE_URL . "storage/" );

define( "IMAGE_PATH", STORAGE_PATH . "images/" );
define( "IMAGE_URL", STORAGE_URL . "images/" );

define( "SYS_PATH", SITE_PATH . "system/" );
define( "SYS_URL", SITE_URL . "system/" );

// System
define( "SYS_MEDIA_URL", SYS_URL . "media/" );
define( "SYS_MEDIA_PATH", SYS_PATH . "media/" );
define( "SYS_TEMPLATE_PATH", SYS_PATH . "templates/" );
define( "INCLUDES_PATH", SYS_PATH . "classes/" );

// User-defined stuff
define( "MEDIA_PATH", SITE_PATH . "user/media/" );
define( "MEDIA_URL", "/user/media/" );

define( "TEMPLATE_PATH", SITE_PATH . "user/templates/" );

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
include_once( INCLUDES_PATH . "twig/lib/Twig/Autoloader.php" );
include_once( INCLUDES_PATH . "template.php" );
include_once( INCLUDES_PATH . "cache.php" );

Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(array(TEMPLATE_PATH,SYS_TEMPLATE_PATH));
$twig = new Twig_Environment( $loader , array(
	'cache' => false,//STORAGE_PATH . "cache/",
	'debug' => true,
	'auto_reload' => true,
	'base_template_class' => 'Template_Extras'
));

// Twig Extras
include_once( SYS_PATH . "twig-extras.php" );

$twig->addExtension(new Twig_Extras());

// Route the request
$router = Router::get_instance();

$router->route();

?>