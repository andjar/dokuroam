<?php
/*
 * Setext style headers:
 *  Header
 *  ======
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_markdowku_headersetext extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'baseonly'; }
    function getPType() { return 'block'; }
    function getSort()  { return 49; }
    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'protected');
    }

    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\n[^\n]+[ \t]*\n=+[ \t]*(?=\n)',
            'base',
            'plugin_markdowku_headersetext');

        $this->Lexer->addSpecialPattern(
            '\n[^\n]+[ \t]*\n-+[ \t]*(?=\n)',
            'base',
            'plugin_markdowku_headersetext');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        $title = preg_replace('/^\n(.+?)[ \t]*\n.*/', '\1', $match);
        $title = trim($title);
        if (preg_match('/^\n(.+?)[ \t]*\n=/', $match))
            $level = 1;
        if (preg_match('/^\n(.+?)[ \t]*\n-/', $match))
            $level = 2;

        if ($handler->status['section'])
            $handler->_addCall('section_close', array(), $pos);
        $handler->status['section_edit_start'] = $pos;
        $handler->status['section_edit_level'] = $level;
        $handler->status['section_edit_title'] = $title;
        $handler->_addCall('header', array($title, $level, $pos), $pos);
        $handler->_addCall('section_open', array($level), $pos);
        $handler->status['section'] = true;
        return true;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
