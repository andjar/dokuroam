<?php
/**
 * Bootstrap Wrapper Plugin: Affix
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_affix extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<affix.*?>(?=.*?</affix>)';
  public $pattern_end    = '</affix>';
  public $tag_name       = 'affix';
  public $tag_attributes = array(

    'offset-top'      => array( 'type'     => 'integer',
                                'values'   => null,
                                'required' => false,
                                'default'  => null),

    'offset-bottom'   => array( 'type'     => 'integer',
                                'values'   => null,
                                'required' => false,
                                'default'  => null),

    'target'          => array( 'type'     => 'string',
                                'values'   => null,
                                'required' => false,
                                'default'  => null),

    'position'        => array( 'type'     => 'string',
                                'values'   => array('fixed', 'absolute'),
                                'required' => false,
                                'default'  => 'fixed'),

    'position-top'    => array( 'type'     => 'string',
                                'values'   => null,
                                'required' => false,
                                'default'  => null),

    'position-bottom' => array( 'type'     => 'string',
                                'values'   => null,
                                'required' => false,
                                'default'  => null),

    'position-left'   => array( 'type'     => 'string',
                                'values'   => null,
                                'required' => false,
                                'default'  => null),

    'position-right'  => array( 'type'     => 'string',
                                'values'   => null,
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

        $top             = $attributes['offset-top'];
        $bottom          = $attributes['offset-bottom'];
        $target          = $attributes['target'];
        $position        = $attributes['position'];
        $position_top    = $attributes['position-top'];
        $position_bottom = $attributes['position-bottom'];
        $position_right  = $attributes['position-right'];
        $position_left   = $attributes['position-left'];

        $html5_data = array();
        $styles     = array();

        if ($position === 'fixed') $position = null;

        if ($position_top && (   ! strstr($position_top, 'px')
                              && ! strstr($position_top, 'em')
                              && ! strstr($position_top, '%'))) {
          $position_top = "{$position_top}px";
        }

        if ($position_bottom && (   ! strstr($position_bottom, 'px')
                                  && ! strstr($position_bottom, 'em')
                                  && ! strstr($position_bottom, '%'))) {
          $position_bottom = "{$position_bottom}px";
        }

        if ($position_right && (   ! strstr($position_right, 'px')
                                && ! strstr($position_right, 'em')
                                && ! strstr($position_right, '%'))) {
          $position_right = "{$position_right}px";
        }

        if ($position_left && (   ! strstr($position_left, 'px')
                                && ! strstr($position_left, 'em')
                                && ! strstr($position_left, '%'))) {
          $position_left = "{$position_left}px";
        }

        if ($top)    $html5_data[] = "data-offset-top=$top ";
        if ($bottom) $html5_data[] = "data-offset-bottom=$bottom ";
        if ($target) $html5_data[] = sprintf('data-target="%s"', $target);

        if ($position)        $styles[] = "position:$position";
        if ($position_top)    $styles[] = "top:$position_top";
        if ($position_bottom) $styles[] = "bottom:$position_bottom";
        if ($position_left)   $styles[] = "left:$position_left";
        if ($position_right)  $styles[] = "right:$position_right";

        $markup = sprintf('<div style="z-index:1024;%s" class="bs-wrap bs-wrap-affix" data-spy="affix" %s>',
          implode(';', $styles), implode(' ', $html5_data));

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= '</div>';
        return true;

    }

    return true;

  }

}
