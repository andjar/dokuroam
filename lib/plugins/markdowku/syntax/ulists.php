<?php
/*
 * Unorderd lists:
 *  * ...
 *  * ...
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
class syntax_plugin_markdowku_ulists extends DokuWiki_Syntax_Plugin {
    function getType()  { return 'container'; }
    function getPType() { return 'block'; }
    function getSort()  { return 9; }
    function getAllowedTypes() {
        return array('formatting', 'substition', 'paragraphs', 'baseonly', 'container');
    }
    
    function connectTo($mode) {
        /* The negative lookahead is for not conflicting with hrs. */
        $this->Lexer->addEntryPattern(
            '\n\n[ ]{0,3}[*+-][ \t](?!(?:[ ]?[*+-][ ]?){2,}[ \t]*\n)',
            $mode,
            'plugin_markdowku_ulists');

        $this->Lexer->addPattern(
            '\n[ \t]*[*+-][ \t](?!(?:[ ]?[*+-][ ]?){2,}[ \t]*\n)',
            'plugin_markdowku_ulists');
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
            '(?:\Z|\n{1,}(?=\n\S)(?!\n[ \t]*[*+-][ \t]))',
            'plugin_markdowku_ulists');
    }
    
    function handle($match, $state, $pos, Doku_Handler $handler) {
        switch ($state) {
            case DOKU_LEXER_ENTER:
                $ReWriter = new Doku_Handler_Markdown_Unordered_List($handler->CallWriter);
                $handler->CallWriter = & $ReWriter;
                $handler->_addCall('list_open', array($match), $pos);
                break;
            case DOKU_LEXER_MATCHED:
                $handler->_addCall('list_item', array($match), $pos);
                break;
            case DOKU_LEXER_UNMATCHED:
                $handler->_addCall('cdata', array($match), $pos);
                break;
            case DOKU_LEXER_EXIT:
                $handler->_addCall('list_close', array(), $pos);
                $handler->CallWriter->process();
                $ReWriter = & $handler->CallWriter;
                $handler->CallWriter = & $ReWriter->CallWriter;
                break;
        }
        return true;
    }
    
    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}

class Doku_Handler_Markdown_Unordered_List extends Doku_Handler_List {
    private $depth = array(0, 4);

    function interpretSyntax($match, &$type) {
        $type="u";
        $listlevel = 1;
        $real_position = 0;
        $logical_position = 0;
        $text = preg_replace('/^\n*/', '', $match);

        while (TRUE) {
            if (preg_match('/^[ ]{'.$this->depth[$listlevel].'}/', substr($text, $real_position)) > 0) {
                $real_position += $this->depth[$listlevel];
                $logical_position += $this->depth[$listlevel];
                $listlevel += 1;
                continue;
            }
            if (preg_match('/^\t/', substr($text, $real_position)) > 0) {
                $real_position += 1;
                $logical_position += 4;
                $listlevel += 1;
                continue;
            }
            if (preg_match('/^[ ]{0,3}[*+-][ \t]/', substr($text, $real_position)) > 0) {
                $this->depth[$listlevel] = strlen(substr($text, $real_position)) - 1;
            }
            break;
        }
        return $listlevel + 1;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
