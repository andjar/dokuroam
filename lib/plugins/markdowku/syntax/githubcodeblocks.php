<?php
/*
 * Github style codeblocks, starting and ending with three backticks, optionally 
 * providing a language to be used for syntax highlighting.
 *
 * ```php
 * ...
 * ```
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_githubcodeblocks extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'protected'; }
    function getPType() { return 'block'; }
    function getSort()  { return 91; }
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\n```[a-z0-9_]*\n.+?\n```(?=\n)',
            $mode,
            'plugin_markdowku_githubcodeblocks');
    }
    
    function handle($match, $state, $pos, Doku_Handler $handler) {
		if (preg_match('/^\n```([a-z0-9_]+)\n/', $match, $matches) > 0) {
			$lang = $matches[1];
		} else {
			$lang = NULL;
		}

		$text = preg_replace('/^```[a-z0-9_]+\n/m', '', $match);
		$text = preg_replace('/^```$/m', '', $text);
		if ($lang)
			$handler->_addCall('file', array($text, $lang, 'snippet.'.$lang), $pos);
		else
			$handler->_addCall('code', array($text, $lang), $pos);
        return true;
    }
    
    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
