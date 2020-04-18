<?php
/**
 * Bootstrap Wrapper Plugin: Panel Group (Accordion)
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_accordion extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<accordion.*?>(?=.*?</accordion>)';
  public $pattern_end    = '</accordion>';
  public $tag_name       = 'accordion';
  public $tag_attributes = array(

    'id'        =>  array('type'     => 'string',
                          'values'   => null,
                          'required' => true,
                          'default'  => null),

    'collapsed' =>  array('type'     => 'boolean',
                          'values'   => array(0,1),
                          'required' => false,
                          'default'  => null),

  );

  function getPType() { return 'block'; }

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes) = $data;

    switch($state) {

      case DOKU_LEXER_ENTER:

        $html_attributes = $this->mergeCoreAttributes($attributes);

        $html_attributes['class'][] = 'bs-wrap bs-wrap-accordion panel-group';

        if ($attributes['collapsed']) {
          $html_attributes['class'][] = 'bs-wrap-accordion-collapsed';
        }

        $markup = sprintf('<div %s>', $this->buildAttributes($html_attributes));

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= '</div>';
        return true;

    }

    return true;

  }

}
