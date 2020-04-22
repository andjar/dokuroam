<?php
/**
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';

class nspages_printerLineBreak extends nspages_printer {
    function __construct($plugin, $mode, $renderer, $data){
        parent::__construct($plugin, $mode, $renderer, $data);
    }

    function _print($tab, $type) {
      $firstItem = true;
        foreach($tab as $item) {
            if ( ! $firstItem ){
                $this->renderer->linebreak();
            }
            $this->renderer->internallink(':'.$item['id'], $item['nameToDisplay']);
            $firstItem = false;
        }
    }

    function printTransition(){
      $this->renderer->cdata(', ');
    }

}
