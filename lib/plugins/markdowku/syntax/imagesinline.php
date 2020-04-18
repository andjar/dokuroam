<?php
/*
 * Inline images: ![source](description "title")
 */
 
if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');
 
class syntax_plugin_markdowku_imagesinline extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'block'; }
    function getSort()  { return 101; }
    
    function connectTo($mode) {
        $this->nested_brackets_re =
            str_repeat('(?>[^\[\]]+|\[', 6).
            str_repeat('\])*', 6);
        $this->Lexer->addSpecialPattern(
            '\!\['.$this->nested_brackets_re.'\]\([ \t]*<?.+?>?[ \t]*(?:[\'"].*?[\'"])?\)',
            $mode,
            'plugin_markdowku_imagesinline');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        if ($state == DOKU_LEXER_SPECIAL) {
            $text = preg_match(
                '/^\!\[('.$this->nested_brackets_re.')\]\([ \t]*<?(.+?)>?[ \t]*(?:[\'"](.*?)[\'"])?[ \t]*?\)$/',
                $match,
                $matches);
            $target = $matches[2] == '' ? $matches[3] : $matches[2];
            $title = $matches[1];
            $handler->media($target.'|'.$title, $state, $pos);
        }
        return true;
    }
    
    function render($mode, Doku_Renderer $renderer, $data) {
        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
