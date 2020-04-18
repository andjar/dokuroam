<?php

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
include_once('headeratx.php');
 
class syntax_plugin_markdowku_blockquotes extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'container'; }
    function getPType() { return 'block'; }
    function getSort()  { return 219; }
    function getAllowedTypes() {
        return array('formatting', 'substition', 'disabled', 'protected', 
        'container');
    }
  
    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
        // (?<=\n)[ \t]*>[ \t]?(?=(?:\n[ ]*[^>]|\Z))
//            '\n[ \t]*>[ \t]?.+?\n(?:.+\n)*',
//            '(?:\n|\A)[ \t]*>(?:[ >\t]*)?', //[ \t]?(?=[^\n]+?\n)',
            '(?:\n|\A)[ \t]*>(?:[ >\t]*)?.*?(?=\n)', //[ \t]?(?=[^\n]+?\n)',
            $mode,
            'plugin_markdowku_blockquotes');

        /* Setext headers need two lines */
        $this->Lexer->addPattern(
            '\n[ \t]*>(?:[ \t>]*>)?[ \t]?[^\n]+?[ \t]*\n[ \t]*>(?:[ \t>]*>)?[ \t]?=+[ \t]*',
            'plugin_markdowku_blockquotes');

        $this->Lexer->addPattern(
            '\n[ \t]*>(?:[ \t>]*>)?[ \t]?[^\n]+?[ \t]*\n[ \t]*>(?:[ \t>]*>)?[ \t]?-+[ \t]*',
            'plugin_markdowku_blockquotes');

        $this->Lexer->addPattern(
//            '\n[ \t]*>(?:[ \t>]*>)?[ \t]?', //[ \t]?(?=[^\n]+?\n)',
            '\n[ \t]*>(?:[ \t>]*>)?[ \t]?.*?(?=\n)', //[ \t]?(?=[^\n]+?\n)',
            'plugin_markdowku_blockquotes');
    }
  
    function postConnect() {
        $this->Lexer->addExitPattern(
            '(?:\n[^>]|\Z)',
            'plugin_markdowku_blockquotes');
    }
  
    function handle($match, $state, $pos, Doku_Handler $handler) {
        global $DOKU_PLUGINS;

        preg_match('/^\n[ \t]*>(?:[ \t>]*>)?[ \t]?/', $match, $quotearg);
        $quoteinarg = preg_replace('/^\n[ \t]*>(?:[ \t>]*>)?[ \t]?/', '', $match);

        if ($state == DOKU_LEXER_ENTER) {
            $ReWriter = new Doku_Handler_Markdown_Quote($handler->CallWriter);
            $handler->CallWriter = & $ReWriter;
            $handler->_addCall('quote_start', $quotearg, $pos);
        } elseif ($state == DOKU_LEXER_EXIT) {
            $handler->_addCall('quote_end', array(), $pos);
            $handler->CallWriter->process();
            $ReWriter = & $handler->CallWriter;
            $handler->CallWriter = & $ReWriter->CallWriter;
        }

        if ($quoteinarg == '') {
            $handler->_addCall('quote_newline', $quotearg, $pos);
        /* ATX headers (headeratx) */
        } elseif (preg_match('/^\#{1,6}[ \t]*.+?[ \t]*\#*/', $quoteinarg)) {
            $plugin =& plugin_load('syntax', 'markdowku_headeratx');
            $plugin->handle($quoteinarg, $state, $pos, $handler);
        /* Horizontal rulers (hr) */
        } elseif (preg_match('/[ ]{0,2}(?:[ ]?_[ ]?){3,}[ \t]*/', $quoteinarg)
                or preg_match('/[ ]{0,2}(?:[ ]?-[ ]?){3,}[ \t]*/', $quoteinarg)
                or preg_match('/[ ]{0,2}(?:[ ]?\*[ ]?){3,}[ \t]*/', $quoteinarg)) {
            $plugin =& plugin_load('syntax', 'markdowku_hr');
            $plugin->handle($quoteinarg, $state, $pos, $handler);
        /* Setext headers (headersetext) */
        } elseif (preg_match('/^[^\n]+?[ \t]*\n[ \t]*>(?:[ \t>]*>)?[ \t]?=+[ \t]*/', $quoteinarg)
                or preg_match('/^[^\n]+?[ \t]*\n[ \t]*>(?:[ \t>]*>)?[ \t]?-+[ \t]*/', $quoteinarg)) {
            $quoteinarg = preg_replace('/(?<=\n)[ \t]*>(?:[ \t>]*>)?[ \t]?/', '', $quoteinarg);
            $plugin =& plugin_load('syntax', 'markdowku_headersetext');
            $plugin->handle($quoteinarg, $state, $pos, $handler);
        } else {
            $handler->_addCall('cdata', array($quoteinarg), $pos);
        }

        return true;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}

class Doku_Handler_Markdown_Quote extends Doku_Handler_Quote {
    function getDepth($marker) {
        $quoteLength = 0;
        $position = 0;
        $text = preg_replace('/^\n*/', '', $marker);
        while (TRUE) {
            if (preg_match('/^[ \t]/', substr($text, $position)) > 0) {
                $position++;
            } elseif (preg_match('/^>/', substr($text, $position)) > 0) {
                $position++;
                $quoteLength++;
            } else {
                break;
            }
        }
        return $quoteLength;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
