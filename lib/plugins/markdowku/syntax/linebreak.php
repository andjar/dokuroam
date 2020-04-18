<?php
/*
 * Linebreaks, determined by two spaces at the line end.
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
class syntax_plugin_markdowku_linebreak extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 139; }
 
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '[ ]{2,}\n',
            $mode,
            'plugin_markdowku_linebreak');
    }
 
    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($match, $state, $pos);
    }
 
    function render($mode, Doku_Renderer $renderer, $data) {
        $renderer->linebreak();
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
