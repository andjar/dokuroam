<?php
/**
 * Bootstrap Wrapper Plugin: Well
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_well extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<well.*?>(?=.*?</well>)';
  public $pattern_end    = '</well>';
  public $tag_name       = 'well';
  public $tag_attributes = array(

    'size' => array('type'     => 'string',
                    'values'   => array('lg', 'sm'),
                    'required' => false,
                    'default'  => null),

  );

  function getPType() { return 'normal'; }


  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes) = $data;

    switch($state) {

      case DOKU_LEXER_ENTER:

        $size   = ($attributes['size']) ? 'well-'.$attributes['size'] : '';
        $markup = sprintf('<div class="bs-wrap bs-wrap-well well %s">', $size);

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= '</div>';
        return true;

    }

    return true;

  }

}
