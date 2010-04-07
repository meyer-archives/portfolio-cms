<?php

// Username and password // TODO: Should come out of the DB
define( "USERNAME", "cassia" );
define( "USERPASS", "44oranges" );

// SQLite DB file name // TODO: Should be randomly generated
define( "DB_FILENAME", "gallery.sqlite3" );

define( "THUMBNAIL_MAX", 100 ); // Max size of the thumbnail
define( "FULLSIZE_MAX", 700 ); // Max image width/height

define( "WATERMARK_IMG", "watermark.png" ); // Relative to /media/images // 300x300px square
define( "ADD_WATERMARK", true );

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