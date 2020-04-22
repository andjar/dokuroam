<?php
/**
 * Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */
if(!defined('DOKU_INC')) die();
require_once 'namespacePreparer.php';
require_once 'pagePreparer.php';

class fileHelper {
    private $files;
    private $data;

    function __construct($data){
        $this->data = $data;
        $this->files = $this->searchFiles($data);
    }

    private function searchFiles(){
        global $conf;
        $opt   = array(
            'depth'     => $this->data['maxDepth'], 'keeptxt'=> false, 'listfiles'=> !$this->data['nopages'],
            'listdirs'  => $this->data['subns'], 'pagesonly'=> true, 'skipacl'=> false,
            'sneakyacl' => true, 'hash'=> false, 'meta'=> true, 'showmsg'=> false,
            'showhidden'=> $this->data['showhidden'], 'firsthead'=> true
        );
        $files = array();
        search($files, $conf['datadir'], 'search_universal', $opt, $this->data['wantedDir']);
        return $files;
    }

    function getPages(){
        $preparer = new pagePreparer($this->data['excludedNS'], $this->data['excludedPages'], $this->data['pregPagesOn'], $this->data['pregPagesOff'], $this->data['pregPagesTitleOn'], $this->data['pregPagesTitleOff'], $this->data['title'], $this->data['sortid'], $this->data['idAndTitle'], $this->data['sortDate'], $this->data['sortByCreationDate']);
        return $this->getFiles($preparer);
    }

    function getSubnamespaces(){
        $preparer = new namespacePreparer($this->data['excludedNS'], $this->data['pregNSOn'], $this->data['pregNSOff'], $this->data['pregNSTitleOn'], $this->data['pregNSTitleOff'], $this->data['title'], $this->data['sortid'], $this->data['idAndTitle'], $this->data['sortDate'], $this->data['sortByCreationDate']);
        return $this->getFiles($preparer);
    }

    private function getFiles($preparer){
        $files = array();
        foreach($this->files as $item) {
           $preparer->prepareFileTitle($item);
           if($preparer->isFileWanted($item, false) && $preparer->isFileWanted($item, true)) {
               $preparer->prepareFile($item);
               $files[] = $item;
           }
        }
        return $files;
    }
}
