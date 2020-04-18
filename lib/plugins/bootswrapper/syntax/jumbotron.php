<?php
/**
 * Bootstrap Wrapper Plugin: Jumbotron
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_jumbotron extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<(?:JUMBOTRON|jumbotron).*?>(?=.*?</(?:JUMBOTRON|jumbotron)>)';
  public $pattern_end    = '</(?:JUMBOTRON|jumbotron)>';
  public $tag_name       = 'jumbotron';
  public $tag_attributes = array(

    'background' => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'color'      => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

  );

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes, $is_block) = $data;

    switch($state) {

      case DOKU_LEXER_ENTER:

        $background = $attributes['background'];
        $color      = $attributes['color'];

        $styles = array();

        if ($background) {
          $styles[] = sprintf('background-image:url(%s)', ml($background));
        }

        if ($color) {
          $styles[] = sprintf('color:%s', hsc($color));
        }

        $markup = sprintf('<div class="bs-wrap bs-wrap-jumbotron jumbotron" style="%s">', implode(';', $styles), $type);

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= '</div>';
        return true;

    }

    return true;

  }

}
