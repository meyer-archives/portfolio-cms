<?php

include( "config.php" );

define( "SITE_PATH", dirname(__FILE__) . "/" );

if( !MOD_REWRITE_ENABLED ){
	// Not used for media!
	define( "SITE_URL", "/index.php/" );
} else {
	define( "SITE_URL", "/" );
}

date_default_timezone_set('America/New_York');

// This is where generated thumbnails and originals are kept
// It can be located anywhere, as long as it's writable and web-accessible
define( "STORAGE_URL", "/storage/" );
define( "STORAGE_PATH", SITE_PATH . "storage/" );

// Original files
define( "UPLOAD_PATH", STORAGE_PATH . "originals/" );
define( "UPLOAD_URL", STORAGE_URL . "originals/" );

// Files accessed through the browser
define( "IMAGE_PATH", STORAGE_PATH . "images/" );
define( "IMAGE_URL", STORAGE_URL . "images/" );

// Includes URL
define( "MEDIA_URL", "/media/" );
define( "MEDIA_PATH", SITE_PATH . "media/" );

// System Stuff
define( "SYS_PATH", SITE_PATH . "system/" );
define( "TEMPLATE_PATH", SITE_PATH . "templates/" );

?>