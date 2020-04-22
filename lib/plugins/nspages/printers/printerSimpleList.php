<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';

class nspages_printerSimpleList extends nspages_printer {
    private $useNumberedList;

    function __construct($plugin, $mode, $renderer, $data, $useNumberedList = false){
        parent::__construct($plugin, $mode, $renderer, $data);
        $this->useNumberedList = $useNumberedList;
    }

    function _print($tab, $type) {
        $this->_openList();
        $this->_printItems($tab);
        $this->_closeList();
    }

    private function _openList() {
        if ( $this->useNumberedList ){
            $this->renderer->listo_open();
        } else {
            $this->renderer->listu_open();
        }
    }

    private function _printItems($tab){
        foreach($tab as $item) {
            $this->_printElement($item);
        }
    }

    private function _closeList() {
        if ( $this->useNumberedList ){
            $this->renderer->listo_close();
        } else {
            $this->renderer->listu_close();
        }
    }
}
