<?php
/**
 * Bootstrap Wrapper Plugin: Tooltip
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Jos Roossien <mail@jroossien.com>
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_popover extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<popover.*?>(?=.*?</popover>)';
  public $pattern_end    = '</popover>';
  public $tag_name       = 'popover';
  public $tag_attributes = array(

    'placement' => array('type'     => 'string',
                          'values'   => array('top', 'bottom', 'left', 'right', 'auto', 'auto top', 'auto bottom', 'auto left', 'auto right'),
                          'required' => true,
                          'default'  => 'right'),

    'title'     => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'content'   => array('type'     => 'string',
                          'values'   => null,
                          'required' => true,
                          'default'  => null),

    'trigger'   => array('type'     => 'multiple',
                          'values'   => array('click', 'hover', 'focus'),
                          'required' => true,
                          'default'  => 'click'),

    'html'      => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => false),

    'animation' => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => true),

    'delay'     => array('type'     => 'integer',
                          'values'   => null,
                          'required' => false,
                          'default'  => 0),

    'delay-show' => array('type'     => 'integer',
                          'values'   => null,
                          'required' => false,
                          'default'  => 0),

    'delay-hide' => array('type'     => 'integer',
                          'values'   => null,
                          'required' => false,
                          'default'  => 0),

  );

  function getPType() { return 'normal'; }

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes) = $data;

    switch($state) {

      case DOKU_LEXER_ENTER:

        $html5_data = array();

        extract($attributes);

        if ($html) {
          $title   = hsc(p_render('xhtml',p_get_instructions($title), $info));
          $content = hsc(p_render('xhtml',p_get_instructions($content), $info));
        }

        if ($trigger)   $html5_data[] = sprintf('data-trigger="%s"',   $trigger);
        if ($animation) $html5_data[] = sprintf('data-animation="%s"', $animation);
        if ($html)      $html5_data[] = sprintf('data-html="%s"',      $html);
        if ($placement) $html5_data[] = sprintf('data-placement="%s"', $placement);
        if ($content)   $html5_data[] = sprintf('data-content="%s"',   $content);
        if ($delay)     $html5_data[] = sprintf('data-delay="%s"',     $delay);

        if (! $delay && ($attributes['delay-hide'] || $attributes['delay-show'])) {

          $delays = array();
          $show   = $attributes['delay-show'];
          $hide   = $attributes['delay-hide'];

          if ($hide) $delays['hide'] = $hide;
          if ($show) $delays['show'] = $show;

          $html5_data[] = sprintf('data-delay=\'%s\'', json_encode($delays));

        }

        $markup = sprintf('<span class="bs-wrap bs-wrap-popover" data-toggle="popover" title="%s" %s>',
            $title, implode(' ', $html5_data));

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= '</span>';
        return true;

      }

    return true;

  }

}
