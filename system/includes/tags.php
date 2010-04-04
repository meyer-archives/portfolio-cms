<?php
class Nav_Tag extends H2o_Node {
	var $slug, $current, $li;
	function __construct($argstring, $parser, $pos = 0){
		$pieces = explode( ",", $argstring );
		$pieces_len = sizeof( $pieces );

		$url = strtolower( $pieces[0] );
		if( $url == "index" ) {
			$url = SITE_URL;
		} else {
			$url = SITE_URL . $url . "/";
		}

		if( $pieces_len == 1 ){
			$url_title = ucfirst( $pieces[0] );
		} elseif( $pieces_len == 2 ) {
			$url_title = $pieces[1];
		}

		$nav_slug == trim( $argstring );

		$current_class = "";
//		if( $current_page_slug == $nav_slug )
//			$current_class = ' class="current"';

		$this->li = "<li{$currentclass}><a href='{$url}'>{$url_title}</a></li>";
	}

	function render($context, $stream) {
		$stream->write( $this->li );
	}
}
h2o::addTag('nav');