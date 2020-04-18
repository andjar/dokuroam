<?php
/*
 * Codespans enclosed with one backtick: `...`
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
class syntax_plugin_markdowku_codespans1 extends DokuWiki_Syntax_Plugin {

    function getType()    { return 'formatting'; }
    function getPType()    { return 'normal'; }
    function getSort()    { return 99; }
    function getAllowedTypes() { return array(); }
 
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '(?<!`)`(?!`).+?(?<!`)`(?!`)',
            $mode,
            'plugin_markdowku_codespans1');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($match);
    }
 
    function render($mode, Doku_Renderer $renderer, $data) {
        $renderer->monospace_open();
        $renderer->cdata(substr($data[0], 1, -1));
        $renderer->monospace_close();
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
