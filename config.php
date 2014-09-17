<?php

// Username and password // TODO: Should come out of the DB
define( "USERNAME", "cassia" );
define( "USERPASS", "44oranges" );

// SQLite DB file name // TODO: Should be randomly generated
define( "DB_FILENAME", "gallery.sqlite3" );

// Image upload constants
define( "THUMBNAIL_MAX", 100 ); // Max size of the thumbnail
define( "FULLSIZE_MAX", 700 ); // Max image width/height

// Watermark image placed in the bottom-left corner of full-size images
define( "WATERMARK_IMG", "watermark.png" ); // Relative to /media/images // 300x300px square
define( "ADD_WATERMARK", true );

// Project URL prefix. Should end in a trailing slash to minify weirdness.
define( "PROJECT_PREFIX", "gallery/" );

$nav = array(
	"index" => array(
		"title" => "Home"
	),
	"gallery" => array(
		"title" => "The Gallery",
		"regex" => "(^gallery$|^gallery/)"
	),
	"about" => array(
		"title" => "About the Artist"
	)
);

?>