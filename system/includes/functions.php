<?php

function logged_in(){
	if( !empty( $_COOKIE["pw_hash"] ) ){
		if(
			$_COOKIE["pw_hash"] ==
			sha1(
				md5( USERPASS ) .
				md5( USERNAME ) .
				"i like salt"
			)
		){
			return true;
		} else {
			die( "Cookie value is incorrect." );
			return false;
		}
	} else {
		return true;
		die( "Cookie is not set." );
		return false;
	}
}

// For use before entering data into the DB
function escape( $string ){
	$string = htmlspecialchars($string,ENT_QUOTES,"UTF-8",false);
	return sqlite_escape_string( $string );
}

function escape_typogrify( $string ){
	$string = typogrify( $string );
	return sqlite_escape_string( $string );
}

// Undo the previous function
// Don't call this on typogrify'd content
function unescape( $string, $htmlescape = true ){
	$string = stripslashes( $string );
	$string = preg_replace("#'{2,}#", "'", $string);
	return $string;
}

// Turns nasty slugs into nice ones
function sluginate( $string, $sep = "_" ){
	return trim( preg_replace("/[^a-z0-9]+/", $sep, strtolower( $string ) ), $sep );
}

// Custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline){
	switch ($errno) {
	case E_USER_ERROR:
		echo "<p style='font-family:\"Helvetica Neue\",Arial,sans-serif'>";
		echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
		echo "	Fatal error on line $errline in file $errfile";
		echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
		echo "</p>";
		exit(1);
		break;

	case E_USER_WARNING:
		echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
		break;

	case E_USER_NOTICE:
		echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
		break;

	default:
		echo "Unknown error type: [$errno] $errstr<br />\n";
		break;
	}

	/* Don't execute PHP internal error handler */
	return true;
}
set_error_handler("custom_error_handler");


function show_error($message, $level=E_USER_NOTICE) {
	trigger_error( $message, $level );
}

function show_404(){
	$t = new Template("error-page");
	$t->set( "error_message", "Page Not Found" );
	$t->set( "error_description", "The page you&rsquo;re looking for cannot be found." );
	$t->render();
}

function jsonReadable($json, $html=FALSE) { 
	$tabcount = 0; 
	$result = ''; 
	$inquote = false; 
	$ignorenext = false; 

	if ($html) { 
		$tab = "&nbsp;&nbsp;"; 
		$newline = "<br/>"; 
	} else { 
		$tab = "\t"; 
		$newline = "\n"; 
	} 

	for($i = 0; $i < strlen($json); $i++) { 
		$char = $json[$i]; 

		if ($ignorenext) { 
			$result .= $char; 
			$ignorenext = false; 
		} else { 
			switch($char) { 
				case '{':
					$tabcount++;
					$result .= $char . $newline . str_repeat($tab, $tabcount);
				break;
				case '}':
					$tabcount--;
					$result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
				break;
				case ',':
					$result .= $char . $newline . str_repeat($tab, $tabcount);
				break;
				case '"':
					$inquote = !$inquote;
					$result .= $char;
				break;
				case '\\':
					if ($inquote) $ignorenext = true;
					$result .= $char;
				break;
				default:
					$result .= $char;
				break;
			} 
		} 
	} 

	return $result; 
}

?>