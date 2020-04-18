<?php
/*
 * Header in ATX style, i.e. '# Header1', '## Header2', ...
 */

if (!defined('DOKU_INC')) die();
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_markdowku_headeratx extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'baseonly'; }
    function getPType() { return 'block'; }
    function getSort()  { return 49; }
    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'protected');
    }
  
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern(
            '\n\#{1,6}[ \t]*.+?[ \t]*\#*(?=\n+)',
            'base',
            'plugin_markdowku_headeratx');
    }
  
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $conf;

        $title = trim($match);
        $level = strspn($title, '#');
        $title = trim($title, '#');
        $title = trim($title);

        if ($level < 1)
            $level = 1;
        elseif ($level > 6)
            $level = 6;

        if ($handler->status['section'])
            $handler->_addCall('section_close', array(), $pos);
        if ($level <= $conf['maxseclevel']) {
            $handler->status['section_edit_start'] = $pos;
            $handler->status['section_edit_level'] = $level;
            $handler->status['section_edit_title'] = $title;
        }
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
