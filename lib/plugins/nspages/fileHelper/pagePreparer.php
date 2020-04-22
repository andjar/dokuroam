<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'filePreparer.php';

class pagePreparer extends filePreparer {
    function __construct($excludedNs, $excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle, $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate){
        parent::__construct($excludedFiles, $pregOn, $pregOff, $pregTitleOn, $pregTitleOff, $useTitle, $sortPageById, $useIdAndTitle, $sortPageByDate, $sortByCreationDate);
        $this->excludedNs = $excludedNs;
    }

    function isFileWanted($file, $useTitle){
        return ($file['type'] != 'd') && parent::isFileWanted($file, $useTitle) && $this->passSubNsfilterInRecursiveMode($file);
    }

    function prepareFileTitle(&$file){
        // Nothing to do: for pages the title is already set
    }

    private function passSubNsfilterInRecursiveMode($file){
        $subNss = explode(':', $file['id']);
        if ( count($subNss) <= 2 ){ //It means we're not in recursive mode
            return true;
        }
        $firstChildSubns = $subNss[1];
        return !in_array($firstChildSubns, $this->excludedNs);
    }

    function prepareFile(&$page){
        $page['nameToDisplay'] = $this->buildNameToDisplay($page['title'], $page['id']);
        $page['sort'] = $this->buildSortAttribute($page['nameToDisplay'], $page['id'], $page['mtime']);
    }

    private function buildNameToDisplay($title, $pageId){
        if($this->useIdAndTitle && $title !== null ){
          return noNS($pageId) . " - " . $title;
        }

        if(!$this->useTitle || $title === null) {
            return noNS($pageId);
        }
        return $title;
    }

    private function buildSortAttribute($nameToDisplay, $pageId, $mtime){
        if($this->sortPageById) {
            return noNS($pageId);
        } else if ( $this->sortPageByDate ){
            return $mtime;
        } else if ($this->sortByCreationDate ){
            $meta = p_get_metadata($pageId);
            return $meta['date']['created'];
        } else {
            return $nameToDisplay;
        }

    }
}
