<?php
/**
 * Nosidebar Plugin: Disables DokuWikis internal sidebar for individual pages
 * 
 * Syntax: ~~NOSIDEBAR~~ 
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Matthias Schulte <dokuwiki@lupo49.de>
 * @version    2013-07-14
 */
 
if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_nosidebar extends DokuWiki_Syntax_Plugin {

    function getType(){ return 'substition'; }
    function getPType(){ return 'normal'; }
    function getSort(){ return 990; }
 
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~NOSIDEBAR~~', $mode, 'plugin_nosidebar');
    }
 
    function handle($match, $state, $pos, Doku_Handler $handler){
        return true;
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == "metadata") {
            // set flag in metadata to disable sidebar in action component
            $renderer->meta['nosidebar'] = true;
        }
        return true;
    } 
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
