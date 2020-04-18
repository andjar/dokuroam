<?php
/*
 * Unescape escaped backslash. \\\\ -> \
 * This is in a separate class as it needs a higher priority than the other 
 * escapes.
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_escapespecialchars extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 61; }
    
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\`',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\*',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\_',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\{',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\}',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\[',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\]',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\(',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\)',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\>',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\#',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\+',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\-',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\-',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\\.',
            $mode,
            'plugin_markdowku_escapespecialchars');
        $this->Lexer->addSpecialPattern(
            '(?<!\\\\)\\\\!',
            $mode,
            'plugin_markdowku_escapespecialchars');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
		$renderer->doc .= substr($data[1], -1);
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
