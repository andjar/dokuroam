<?php
/*
 * Italic text enclosed by asterisks, i.e. *...*
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_italicasterisk extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'formatting'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 79; }
    function getAllowedTypes() {
        return Array('formatting', 'substition');
    }
    
    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
            '(?<![\\\\])\*(?![ *])(?=(?:(?!\n\n).)+?(?<![\\\\ *])\*)',
            $mode,
            'plugin_markdowku_italicasterisk');
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
            '(?<![\\\\* ])\*',
            'plugin_markdowku_italicasterisk');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        if ($data[0] == DOKU_LEXER_ENTER)
            $renderer->emphasis_open();
        elseif ($data[0] == DOKU_LEXER_EXIT)
            $renderer->emphasis_close();
        else
            $renderer->cdata($data[1]);

        return true;
    }
}
