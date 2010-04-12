<?php

abstract class Template_Extras extends Twig_Template{
	protected $context = null;
	protected $router;
	protected $portfolio;

	public function __construct(){
		$this->router = Router::get_instance();
	}

	public function setContext($context){
		$this->context = $context;
	}

	public function getContext(){
		return $this->context;
	}
}

class Twig_Extras extends Twig_Extension{
	public function getName(){
		return 'extras';
	}

	public function getFilters(){
		return array(
			'slice' => new Twig_Filter_Function('twig_slice_filter'),
			'first' => new Twig_Filter_Function('twig_first_filter')
		);
	}

	public function getTokenParsers(){
		return array(
			new Nav_TokenParser(),
			new Now_TokenParser()
		);
	}
}

function twig_slice_filter($array, $offset, $length){
	return array_slice( $array, $offset, $length, true );
}

function twig_first_filter($array){
	if( sizeof( $array ) > 0 )
		return array_slice($array);
	return array();
}

class Nav_TokenParser extends Twig_TokenParser{
	public function parse(Twig_Token $token){
		$lineno = $token->getLine();
		$link_slug = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new Nav_Node($link_slug, $lineno);
	}

	public function getTag(){
		return 'nav';
	}
}

class Nav_Node extends Twig_Node{
	protected $slug;
	protected $title;
	protected $regex;
	protected $url;
 
	public function __construct($link_slug, $lineno){
		parent::__construct($lineno);

		global $nav;

		if( !empty( $nav[$link_slug] ) && $nav_item = $nav[$link_slug] ){
			$this->slug = $link_slug;
			$this->title = !empty( $nav_item["title"] ) ? $nav_item["title"] : '$nav_item["title"] not set';
			$this->regex = !empty( $nav_item["regex"] ) ? $nav_item["regex"] : "^".($link_slug=="index"?"":$link_slug)."$";
			$this->url = !empty( $nav_item["url"] ) ? $nav_item["url"] : $link_slug;
			if( $this->url == "index" ){
				$this->url = SITE_URL;
			} elseif( substr( $this->url, 0, 7 ) == "http://" ) {
				// External link
			} else {
				$this->url = SITE_URL . $this->url . "/";
			}
		} else {
			$this->slug = "";
			$this->url = "#";
			$this->title = "{% Nav $link_slug %}";
			$this->regex = "^\s$";
		}
	}
 
	public function compile($compiler){
		$compiler
			->addDebugInfo($this)
//			->write('echo "<li class=\"nav-link\"><a href=\"".SITE_URL."\">'.$this->slug.'</a></li>"')

			->write('echo "<li class=\"nav-link".(preg_match("#'.$this->regex.'#",$this->router->url->slug)?" current":"")."\">')
			->write('<a href=\"'.$this->url.'\">'.$this->title.'</a>')
			->write('</li>"')

			->raw(";\n")
		;
	}
}


class Now_TokenParser extends Twig_TokenParser{
	public function parse(Twig_Token $token){
		$lineno = $token->getLine();
		$date_string = $this->parser->getStream()->expect(Twig_Token::STRING_TYPE)->getValue();
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);

		return new Now_Node($date_string, $lineno);
	}

	public function getTag(){
		return 'now';
	}
}

class Now_Node extends Twig_Node{
	protected $date_string;
 
	public function __construct($date_string, $lineno){
		parent::__construct($lineno);
		$this->date_string = $date_string;
	}
 
	public function compile($compiler){
		$compiler
			->addDebugInfo($this)
//			->write('echo "<li class=\"nav-link\"><a href=\"".SITE_URL."\">'.$this->slug.'</a></li>"')

			->write('echo date("' . $this->date_string . '");')

			->raw(";\n")
		;
	}
}


?>