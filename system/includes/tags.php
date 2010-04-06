<?php

class Nav_Twig_Extension extends Twig_Extension{
	public function getTokenParsers(){
		return array(new Nav_TokenParser());
	}

	public function getName(){
		return;
	}
}

class Nav_TokenParser extends Twig_TokenParser{
	public function parse(Twig_Token $token){
		$lineno = $token->getLine();
		$name = $this->parser->getStream()->expect(Twig_Token::NAME_TYPE)->getValue();
		$this->parser->getStream()->expect(Twig_Token::NAME_TYPE, 'as');
		$value = $this->parser->getExpressionParser()->parseExpression();
 
		$this->parser->getStream()->expect(Twig_Token::BLOCK_END_TYPE);
 
		return new Nav_Node($name, $value, $lineno, $this->getTag());
	}
 
	public function getTag(){
		return 'nav';
	}
}

class Nav_Node extends Twig_Node{
	protected $name;
	protected $value;
 
	public function __construct($name, Twig_Node_Expression $value, $lineno){
		parent::__construct($lineno);
 
		$this->name = $name;
		$this->value = $value;
	}
 
	public function compile($compiler){
		$compiler
			->addDebugInfo($this)
			->write('$context[\''.$this->name.'\'] = ')
			->subcompile($this->value)
			->raw(";\n")
		;
	}
}

?>