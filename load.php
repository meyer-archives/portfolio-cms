<?php

include( "config.php" );

define( "SITE_PATH", dirname(__FILE__) . "/" );

if( !MOD_REWRITE_ENABLED ){
	// Not to be used for media (obviously)!!
	define( "SITE_URL", "/index.php/" );
} else {
	define( "SITE_URL", "/" );
}

// API
define( "API_URL", SITE_URL . "api/" );

// Fix that dumb PHP warning
date_default_timezone_set('America/New_York');

// All user-generated files
// It can be located anywhere, as long as it's writable and web-accessible
define( "STORAGE_URL", "/storage/" );
define( "STORAGE_PATH", SITE_PATH . "storage/" );

// Files accessed through the browser
define( "IMAGE_PATH", STORAGE_PATH . "images/" );
define( "IMAGE_URL", STORAGE_URL . "images/" );

// System Stuff
define( "SYS_PATH", SITE_PATH . "system/" );

// User Media
define( "MEDIA_URL", "/media/" );
define( "MEDIA_PATH", SITE_PATH . "media/" );
define( "TEMPLATE_PATH", MEDIA_PATH . "templates/" );

// System Media URL
define( "SYS_MEDIA_URL", "/system/media/" );
define( "SYS_MEDIA_PATH", SITE_PATH . "system/media/" );

// Includes path
define( "INCLUDES_PATH", SYS_PATH . "includes/" );

?>