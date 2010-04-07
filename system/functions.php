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

function typogrify( $text, $its_a_heading = false ){
	$text = htmlentities($text,ENT_NOQUOTES,"UTF-8",false);
	if( !$its_a_heading )
		$text = Markdown( $text );
    $text = amp( $text );
    $text = widont( $text );
    $text = SmartyPants( $text );
    $text = caps( $text );
    $text = initial_quotes( $text );
    $text = dash( $text );
    
    return $text;
}

// For use before entering data into the DB
function escape( $text ){
	$text = htmlentities($text,ENT_QUOTES,"UTF-8",false);
	return sqlite_escape_string( $text );
}

function escape_typogrify( $text ){
	$text = strip_tags( $text );
	$text = typogrify( $text );
	return sqlite_escape_string( $text );
}

function escape_heading( $text ){
	$text = strip_tags( $text );
	$text = typogrify( $text, true );
	return sqlite_escape_string( $text );
}

function unescape( $text ){
	$text = preg_replace("#'{2,}#", "'", $text);
	return $text;
}

// Turns nasty slugs into nice ones
function sluginate( $text, $sep = "-" ){
	return trim( preg_replace("/([^\w]|^the\s)+/i", $sep, strtolower( html_entity_decode( $text ) ) ), $sep );
}

// Custom error handler
function custom_error_handler($errno, $errstr, $errfile, $errline){
	if( !empty( $errline ) )
		$errfile = ( substr( $errfile, 0, strlen(SITE_PATH) ) == SITE_PATH ) ? "/" . substr( $errfile, strlen(SITE_PATH) ) : $errfile;
	$error_start = "<p style='margin:3px;color:#000;opacity:.6;border:1px solid #000;background:#eee;padding:3px 5px;font-family:\"Helvetica Neue\",Arial,sans-serif'>";
	$error_end = "</p>";
	switch ($errno) {
	case E_USER_ERROR:
		echo $error_start;
		echo "<b>Error</b>: [$errno] $errstr &mdash; ";
		echo "Fatal error on line $errline in file $errfile";
		echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")";
		echo $error_end;
		exit();
		break;

	case E_USER_WARNING:
		echo $error_start;
		echo "<b>Warning</b>: $errfile:$errline [$errno] $errstr";
		echo $error_end;
		break;

	case E_USER_NOTICE:
		echo $error_start;
		echo "<b>Notice</b>: $errfile:$errline [$errno] $errstr";
		echo $error_end;
		break;

	default:
//		echo $error_start;
//		echo "<b>Unknown error type</b>: $errfile:$errline [$errno] $errstr";
//		echo $error_end;
		break;
	}

	/* Don't execute PHP internal error handler */
	return true;
}
set_error_handler("custom_error_handler");


function show_error($message, $level=E_USER_NOTICE) {
	trigger_error( $message, $level );
}

function template_exists( $template_name ){
	return file_exists( TEMPLATE_PATH . $template_name . ".php" );
}

function is_current( $slug ){
	$r = Router::get_instance();
	if( $slug == $r->slug ){
		die("YES");
	} else {
		die("NO");
	}
}

function json_readable($json, $html=FALSE) { 
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