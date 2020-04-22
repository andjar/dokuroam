<?php
/**
* Plugin nspages : Displays nicely a list of the pages of a namespace
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 */

if(!defined('DOKU_INC')) die();
require_once 'printer.php';

class nspages_printerPictures extends nspages_printer {
    private static $_dims = array('w' => 350, 'h' => 220);

    private $_displayModificationDate;

    function __construct($plugin, $mode, $renderer, $data){
        parent::__construct($plugin, $mode, $renderer, $data);
        $this->_displayModificationDate = $data['modificationDateOnPictures'];
        $this->_defaultPicture = $data['defaultPicture'];
    }

    function _print($tab, $type) {
        $this->renderer->doc .= '<div class="nspagesPicturesModeMain">';
        foreach($tab as $item) {
                $picture = $this->_getFirstImage($item['id']);
                $url = wl($item['id']);

                // TODO: implement support for non-HTML mode
                //       Note that, wrt indexing, it's not an issue to build a <a> ourselves instead of using the api
                //       because non xhtml mode (eg: "metadata" mode) isn't plugged on this xhtml specific code
                $this->renderer->doc .= '<a href="'. $url .'" title="'.$item['nameToDisplay'].'">';
                $this->renderer->doc .= '<div class="nspagesPicturesModeImg" style="background-image:url('. $picture .')">';
                $this->renderer->doc .= '<span class="nspagesPicturesModeTitle">'.$item['nameToDisplay'];
                if ( $this->_displayModificationDate ){
                    $this->renderer->doc .= '</span><span class="nspagesPicturesDate">' . date('d/m/Y', $this->_getModificationDate($item['id']));
                }
                $this->renderer->doc .= '</span></div></a>';
        }
        $this->renderer->doc .= '</div>';
    }

    private function _getFirstImage($pageId){
      $meta = p_get_metadata($pageId);
      $picture = $meta['relation']['firstimage'];
      if ( $picture != "" ){
          return ml($picture, self::$_dims, true);
      } else {
          if ( $this->_defaultPicture == '' ){
                return "lib/tpl/dokuwiki/images/logo.png";
          } else {
                return ml($this->_defaultPicture, self::$_dims, true);
          }
      }
    }

    private function _getModificationDate($pageId){
        $meta = p_get_metadata($pageId);
        return $meta['date']['modified'];
    }
}
