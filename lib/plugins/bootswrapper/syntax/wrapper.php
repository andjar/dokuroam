<?php
/**
 * Bootstrap Wrapper Plugin: Generic Wrapper (span or div)
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_wrapper extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start = '<(?:WRAPPER|wrapper).*?>(?=.*?</(?:WRAPPER|wrapper)>)';
  public $pattern_end   = '</(?:WRAPPER|wrapper)>';
  public $tag_name      = 'wrapper';

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;

    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes, $is_block) = $data;

    global $wrapper_tag;

    switch($state) {

      case DOKU_LEXER_ENTER:

        $wrapper_tag  = ($is_block) ? 'div' : 'span';
        $wrap_classes = $attributes['class'];

        $wrap_classes[]  = 'bs-wrapper';

        $markup = sprintf('<%s %s>', $wrapper_tag, $this->buildAttributes($attributes, array('class' => $wrap_classes)));

        $renderer->doc .= $markup;

        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= "</$wrapper_tag>";
        return true;

    }

    return false;

  }

}
