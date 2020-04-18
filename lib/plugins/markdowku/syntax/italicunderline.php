<?php
/*
 * Italic text enclosed in underlines: _..._
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_italicunderline extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'formatting'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 79; }
    function getAllowedTypes() {
        return Array('formatting', 'substition');
    }
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
            '(?<![\\\\])_(?![ _])(?=(?:(?!\n\n).)+?[^\\\\ _]_)',
            $mode,
            'plugin_markdowku_italicunderline');
//        $this->Lexer->addSpecialPattern(
//            '\w+_\w+_\w[\w_]*',
//            $mode,
//            'plugin_markdowku_italicunderline');
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
            '(?<![\\\\_ ])_',
            'plugin_markdowku_italicunderline');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    function render($mode, Doku_Renderer $renderer, $data) {

        if ($data[0] == DOKU_LEXER_ENTER)
            $renderer->emphasis_open();
        elseif ($data[0] == DOKU_LEXER_EXIT)
            $renderer->emphasis_close();
        elseif ($data[0] == DOKU_LEXER_UNMATCHED)
            $renderer->cdata($data[1]);
        elseif ($data[0] == DOKU_LEXER_SPECIAL)
            $renderer->cdata($data[1]);

        return true;
    }
}
