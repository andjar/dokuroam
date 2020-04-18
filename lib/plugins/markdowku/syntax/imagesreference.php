<?php
/*
 * Reference links, i.e.
 *  ... [name][id] ...
 *  ... [id][] ...
 *  ...
 *  [id]: http://example.com (handled by markdowku_references)
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_markdowku_imagesreference extends DokuWiki_Syntax_Plugin {

    function getType()  { return 'substition'; }
    function getPType() { return 'normal'; }
    function getSort()  { return 102; }

    function connectTo($mode) {
        $this->nested_brackets_re =
            str_repeat('(?>[^\[\]]+|\[', 3).
            str_repeat('\])*', 3);
        $this->Lexer->addSpecialPattern(
            '\!\['.$this->nested_brackets_re.'\][ ]?(?:\n[ ]*)?\[[^\n]*?\]',
            $mode,
            'plugin_markdowku_imagesreference');
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        return array($state, $match);
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;
        preg_match(
            '/^\!\[('.$this->nested_brackets_re.')\][ ]?(?:\n[ ]*)?\[(.*?)\]$/',
            $data[1],
            $matches);

        $title = $matches[1];

        if ($matches[2] == '')
            $rid = $matches[1];
        else
            $rid = $matches[2];

        $rid = preg_replace("/ /", ".", $rid);
        $target = p_get_metadata($ID, 'markdowku_references_'.$rid, METADATA_RENDER_USING_CACHE);
        if ($target == '')
            $renderer->cdata($data[1]);
        else
            $renderer->_media($target, $title);

        return true;
    }
}
//Setup VIM: ex: et ts=4 enc=utf-8 :
