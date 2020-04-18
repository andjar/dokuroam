<?php
/*
 * Horizontal rulers:
 *  * * *
 *  ---
 *  ___
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
class syntax_plugin_markdowku_hr extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'container'; }
    function getPType() { return 'block'; }
    function getSort()  { return 8; } /* Before list block parsing. */
 
    function connectTo($mode) {
        /* We use two newlines, as we don't want to conflict with setext header 
         * parsing, but also have to be before list blocks. */
        $this->Lexer->addSpecialPattern(
            '\n[ ]{0,2}(?:[ ]?\*[ ]?){3,}[ \t]*(?=\n)',
            $mode,
            'plugin_markdowku_hr');

        $this->Lexer->addSpecialPattern(
            '\n[ ]{0,2}(?:[ ]?-[ ]?){3,}[ \t]*(?=\n)',
            $mode,
            'plugin_markdowku_hr');

        $this->Lexer->addSpecialPattern(
            '\n[ ]{0,2}(?:[ ]?_[ ]?){3,}[ \t]*(?=\n)',
            $mode,
            'plugin_markdowku_hr');
    }
 
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $handler->_addCall('hr', array(), $pos);
        return true;
    }
 
    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
