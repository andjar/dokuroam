<?php
/**
 * Bootstrap Wrapper Plugin: Alert
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_alert extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<(?:ALERT|alert).*?>(?=.*?</(?:ALERT|alert)>)';
  public $pattern_end    = '</(?:ALERT|alert)>';
  public $tag_name       = 'alert';
  public $tag_attributes = array(

    'type'      => array('type'     => 'string',
                         'values'   => array('success', 'info', 'warning', 'danger'),
                         'required' => true,
                         'default'  => 'info'),

    'dismiss'   => array('type'     => 'boolean',
                         'values'   => array(0, 1),
                         'required' => false,
                         'default'  => false),

    'icon'      => array('type'     => 'string',
                         'values'   => null,
                         'required' => false,
                         'default'  => null),
  );

  function getPType(){ return 'block'; }

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes) = $data;

    switch($state) {

      case DOKU_LEXER_ENTER:

        extract($attributes);

        $html_attributes = $this->mergeCoreAttributes($attributes);
        $html_attributes['class'][] = 'bs-wrap alert';
        $html_attributes['class'][] = "alert-$type";
        $html_attributes['role'] = 'alert';

        if ($dismiss) $html_attributes['class'][] = 'alert-dismissible';

        $markup = sprintf('<div %s>', $this->buildAttributes($html_attributes));

        if ($dismiss) {
            $markup .= '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
        }

        if ($icon) {
          $markup .= sprintf('<i class="%s"></i> ', $icon);
        }

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= '</div>';
        return true;

    }

    return true;

  }

}
