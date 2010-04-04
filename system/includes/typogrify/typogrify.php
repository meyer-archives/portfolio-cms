<?php

/*

Copyright (c) 2007, Hamish Macpherson
All rights reserved.
Redistribution and use in source and binary forms, with or without modification, are permitted provided that the following conditions are met:

* Redistributions of source code must retain the above copyright notice, this list of conditions and the following disclaimer.
* Redistributions in binary form must reproduce the above copyright notice, this list of conditions and the following disclaimer in the documentation and/or other materials provided with the distribution.
* Neither the name of the php-typogrify nor the names of its contributors may be used to endorse or promote products derived from this software without specific prior written permission.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

*/

function amp( $text ){
	$amp_finder = "/(\s|&nbsp;)(&|&amp;|&\#38;|&#038;)(\s|&nbsp;)/";
	return preg_replace($amp_finder, '\\1<span class="amp">&amp;</span>\\3', $text);
}

function dash( $text ){
	$dash_finder = "/(\s|&nbsp;|&thinsp;)*(&mdash;|&ndash;|&#x2013;|&#8211;|&#x2014;|&#8212;)(\s|&nbsp;|&thinsp;)*/";
	return preg_replace($dash_finder, '&thinsp;\\2&thinsp;', $text);
}

function _cap_wrapper( $matchobj ){
	if ( !empty($matchobj[2]) ){
		return sprintf('<span class="caps">%s</span>', $matchobj[2]);
	} else {
		$mthree = $matchobj[3];
		if ( ($mthree{strlen($mthree)-1}) == " " ) {
			$caps = substr($mthree, 0, -1);
			$tail = ' ';
		} else {
			$caps = $mthree;
			$tail = '';
		}			 
		return sprintf('<span class="caps">%s</span>%s', $caps, $tail);
	}
}

function caps( $text ){
	$cap_finder =
	"/(" .
	"(\b[A-Z\d]*[A-Z]\d*[A-Z][A-Z\d]*\b)|" .
	"(\b[A-Z]+\.\s?(?:[A-Z]+\.\s?)+)" .
	"(?:\s|\b|$)" .
	")/x";

	return preg_replace( $cap_finder, '<span class="caps">$1</span>', $text );
}

function _quote_wrapper( $matchobj )
{
	if ( !empty($matchobj[7]) )
	{
		$classname = "dquo quo";
		$quote = $matchobj[7];
	}
	else
	{
		$classname = "squo quo";
		$quote = $matchobj[8];
	}
	return sprintf('%s<span class="%s">%s</span>', $matchobj[1], $classname, $quote);
}

/**
 * initial_quotes
 *
 * Wraps initial quotes in ``class="dquo"`` for double quotes or  
 * ``class="quo"`` for single quotes. Works in these block tags ``(h1-h6, p, li)``
 * and also accounts for potential opening inline elements ``a, em, strong, span, b, i``
 * Optionally choose to apply quote span tags to Gullemets as well.
 */
function initial_quotes( $text, $do_guillemets = false )
{
	$quote_finder = "/((<(p|h[1-6]|li)[^>]*>|^)						# start with an opening p, h1-6, li or the start of the string
					\s*												# optional white space! 
					(<(a|em|span|strong|i|b)[^>]*>\s*)*)			# optional opening inline tags, with more optional white space for each.
					((\"|&ldquo;|&\#8220;)|('|&lsquo;|&\#8216;))	# Find me a quote! (only need to find the left quotes and the primes)
																	# double quotes are in group 7, singles in group 8
					/ix";
	
	if ($do_guillemets)
	{
		$quote_finder = "/((<(p|h[1-6]|li)[^>]*>|^)											# start with an opening p, h1-6, li or the start of the string
						\s*																	# optional white space! 
						(<(a|em|span|strong|i|b)[^>]*>\s*)*)								# optional opening inline tags, with more optional white space for each.
						((\"|&ldquo;|&\#8220;|\xAE|&\#171;|&laquo;)|('|&lsquo;|&\#8216;))	 # Find me a quote! (only need to find the left quotes and the primes) - also look for guillemets (>> and << characters))
																							# double quotes are in group 7, singles in group 8
						/ix";
	}
					
	return preg_replace_callback($quote_finder, "_quote_wrapper", $text);
}

/**
 * widont
 * 
 * Replaces the space between the last two words in a string with ``&nbsp;``
 * Works in these block tags ``(h1-h6, p, li)`` and also accounts for 
 * potential closing inline elements ``a, em, strong, span, b, i``
 * 
 * Empty HTMLs shouldn't error
 */
/* function widont( $text )
{
	return preg_replace( '|([^\s])\s+([^\s]+)\s*$|', '$1&nbsp;$2', $text);
}
*/

function widont( $text ){
	$widont_finder = "/([^\s])\s+(((<(a|span|i|b|em|strong|acronym|caps|sub|sup|abbr|big|small|code|cite|tt)[^>]*>)*\s*[^\s<>]+)(<\/(a|span|i|b|em|strong|acronym|caps|sub|sup|abbr|big|small|code|cite|tt)>)*[^\s<>]*\s*(<\/(p|h[1-6]|li)>|$))/i";
					
	return preg_replace($widont_finder, '$1&nbsp;$2', $text);
}

?>