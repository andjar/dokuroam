<?php
/**
* Bootstrap Wrapper Plugin: Button
* 
* @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
* @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
* @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
*/

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_button extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<(?:btn|button).*?>(?=.*?</(?:btn|button)>)';
  public $pattern_end    = '</(?:btn|button)>';
  public $tag_name       = 'button';
  public $tag_attributes = array(

    'type'      => array('type'     => 'string',
                          'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'link'),
                          'required' => true,
                          'default'  => 'default'),

    'size'      => array('type'     => 'string',
                          'values'   => array('lg', 'sm', 'xs'),
                          'required' => false,
                          'default'  => null),

    'icon'      => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'collapse'  => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'modal'     => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'block'     => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => null),

    'disabled'  => array('type'     => 'boolean',
                          'values'   => array(0, 1),
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

        $html_attributes = $this->mergeCoreAttributes($attributes);
        $html_attributes['class'][] = 'bs-wrap bs-wrap-button';

        foreach (array_keys($this->tag_attributes) as $attribute) {
          if (isset($attributes[$attribute])) {
            $html_attributes["data-btn-$attribute"] = $attributes[$attribute];
          }
        }

        $markup = sprintf('<span %s>', $this->buildAttributes($html_attributes));

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= "</span>";
        return true;

    }

    return true;

  }

}
