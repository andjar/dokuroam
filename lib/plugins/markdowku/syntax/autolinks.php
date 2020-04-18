<?php
/*
 * Autolinks enclosed in <...>
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
class syntax_plugin_markdowku_autolinks extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 102; }
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '<(?:https?|ftp|mailto):[^\'">\s]+?>',
            $mode,
            'plugin_markdowku_autolinks'
        );
    }
    
    function handle($match, $state, $pos, Doku_Handler $handler) {
		if (preg_match('/^<mailto:/', $match)) {
            $match = substr($match, 8, -1);
   	        $handler->_addCall('emaillink', array($match, NULL), $pos);
		} else {
            $match = substr($match, 1, -1);
   	        $handler->_addCall('externallink', array($match, NULL), $pos);
		}

        return true;
    }
    
    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
