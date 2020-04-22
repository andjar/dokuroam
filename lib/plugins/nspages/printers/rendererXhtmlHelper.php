<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();

class rendererXhtmlHelper {
    private $renderer;
    private $percentWidth;
    private $plugin;
    private $anchorName;

    function __construct($renderer, $nbCols, $plugin, $anchorName){
        $this->renderer =& $renderer;
        $this->percentWidth = $this->buildWidth($nbCols);
        $this->plugin = $plugin;
        $this->anchorName = $anchorName;
    }

    private function buildWidth($nbCols){
        return (100 / $nbCols) . '%';
    }

    function printHeaderChar($char, $continued = false){
        $text = $char;
        if ( $continued ){
            $text .= $this->plugin->getLang('continued');
        }

        $this->renderer->doc .= '<div '
            . $this->fullAnchor($char, $continued)
            . 'class="catpagechars';
        if ( $continued ){
            $this->renderer->doc .= ' continued';
        }
        $this->renderer->doc .= '">' . $text . "</div>\n";
    }

    private function fullAnchor($char, $continued){
        if ( $continued === true || is_null($this->anchorName) ){
            return '';
        }

        return 'id="nspages_' . $this->anchorName . '_' . $char . '" ';
    }

    function openColumn(){
        $this->renderer->doc .= "\n".'<div class="catpagecol" style="width: '.$this->percentWidth.'" >';
    }

    function closeColumn(){
        $this->renderer->doc .= "</div>\n";
    }

    function openListOfItems(){
        $this->renderer->doc .= "<ul class=\"nspagesul\">\n";
    }

    function closeListOfItems(){
        $this->renderer->doc .= '</ul>';
    }
}
