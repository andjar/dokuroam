<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';
require_once 'rendererXhtmlHelper.php';

class nspages_printerNice extends nspages_printer {
    private $nbCols;
    private $anchorName;

    function __construct($plugin, $mode, $renderer, $nbCols, $anchorName, $data){
        parent::__construct($plugin, $mode, $renderer, $data);
        if ( $this->mode !== 'xhtml' ){
          throw Exception('nspages_printerNice can only work in xhtml mode');
        }
        $this->nbCols = $this->_computeActualNbCols($nbCols);
        $this->anchorName = $anchorName;
    }

    private function _computeActualNbCols($nbCols){
        $nbCols = (int) $nbCols;
        if(!isset($nbCols) || is_null($nbCols) || $nbCols < 1) {
            $nbCols = 3;
        }
        return $nbCols;
    }

    function _print($tab, $type) {
        $nbItemsPrinted = 0;

        $nbItemPerColumns = $this->_computeNbItemPerColumns(sizeof($tab));
        $actualNbCols = count($nbItemPerColumns);
        $helper = new rendererXhtmlHelper($this->renderer, $actualNbCols, $this->plugin, $this->anchorName);

        $helper->openColumn();
        $firstCharOfLastAddedPage = $this->_firstChar($tab[0]);

        $helper->printHeaderChar($firstCharOfLastAddedPage);
        $helper->openListOfItems();

        $idxCol = 0;
        foreach($tab as $item) {
            //change to the next column if necessary
            if($nbItemsPrinted == $nbItemPerColumns[$idxCol]) {
                $idxCol++;
                $helper->closeListOfItems();
                $helper->closeColumn();
                $helper->openColumn();

                $newLetter = $this->_firstChar($item);
                if($newLetter != $firstCharOfLastAddedPage) {
                    $firstCharOfLastAddedPage = $newLetter;
                    $helper->printHeaderChar($firstCharOfLastAddedPage);
                } else {
                    $helper->printHeaderChar($firstCharOfLastAddedPage, true);
                }
                $helper->openListOfItems();
            }

            $newLetter = $this->_firstChar($item);
            if($newLetter != $firstCharOfLastAddedPage) {
                $firstCharOfLastAddedPage = $newLetter;
                $helper->closeListOfItems();
                $helper->printHeaderChar($firstCharOfLastAddedPage);
                $helper->openListOfItems();
            }

            $this->_printElement($item);
            $nbItemsPrinted++;
        }
        $helper->closeListOfItems();
        $helper->closeColumn();
    }

    private function _firstChar($item) {
        return utf8_strtoupper(utf8_substr($item['sort'], 0, 1));
    }

    /**
     * Compute the number of element to display per column
     * When $nbItems / $nbCols isn't an int, we make sure, for aesthetic reasons,
     * that the first are the ones which have the more items
     * Moreover, if we don't have enought items to display, we may choose to display less than the number of columns wanted
     *
     * @param int $nbItems The total number of items to display
     * @return an array which contains $nbCols int.
     */
    private function _computeNbItemPerColumns($nbItems) {
        $result = array();

        if($nbItems < $this->nbCols) {
            for($idx = 0; $idx < $nbItems; $idx++) {
                $result[] = $idx + 1;
            }
            return $result;
        }

        $collength    = $nbItems / $this->nbCols;
        $nbItemPerCol = array();
        for($idx = 0; $idx < $this->nbCols; $idx++) {
            $nbItemPerCol[] = ceil(($idx + 1) * $collength) - ceil($idx * $collength);
        }
        rsort($nbItemPerCol);

        $result[] = $nbItemPerCol[0];
        for($idx = 1; $idx < $this->nbCols; $idx++) {
            $result[] = end($result) + $nbItemPerCol[$idx];
        }

        return $result;
    }
}
