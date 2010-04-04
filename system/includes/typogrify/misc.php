<?php

// Miscellaneous functions

define( "SPACE", "NBSP".md5(time()) );

function widont($str = ''){
	return preg_replace( '|([^\s])\s+([^\s]+)\s*$|', '$1&nbsp;$2', $str);
}

function amp( $text ){
	$amp_finder = "/(\s|&nbsp;)(&|&amp;|&\#38;|&#038;)(\s|&nbsp;)/";
	return preg_replace($amp_finder, '\\1<span class="amp">&amp;</span>\\3', $text);
}

function CapIt( $text ){
	return preg_replace(
		"#([\s|&nbsp;])([A-Z]{2,}+)([\s|&nbsp;])#",
		'$1<span class="caps">$2</span>$3',
		$text
	);
}

?>