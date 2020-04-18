<?php
/**
 * Section close helper of the Wrap Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael@content-space.de>
 */

if(!defined('DOKU_INC')) die();

if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

class syntax_plugin_wrap_closesection extends DokuWiki_Syntax_Plugin {

    function getType(){ return 'substition';}
    function getPType(){ return 'block';}
    function getSort(){ return 195; }

    /**
     * Dummy handler, this syntax part has no syntax but is directly added to the instructions by the div syntax
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $indata) {
        if($mode == 'xhtml'){
            /** @var Doku_Renderer_xhtml $renderer */
            $renderer->finishSectionEdit();
            return true;
        }
        return false;
    }


}

