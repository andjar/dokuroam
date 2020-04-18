<?php
/**
 * Bootstrap Wrapper Plugin: Callout
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_callout extends syntax_plugin_bootswrapper_bootstrap {

    public $pattern_start  = '<callout.*?>(?=.*?</callout>)';
    public $pattern_end    = '</callout>';
    public $tag_name       = 'callout';
    public $tag_attributes = array(

      'type' =>  array('type'     => 'string',
                       'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger', 'question', 'tip'),
                       'required' => true,
                       'default'  => 'default'),

      'title' => array('type'     => 'string',
                       'values'   => null,
                       'required' => false,
                       'default'  => null),

      'color' => array('type'     => 'string',
                       'values'   => null,
                       'required' => false,
                       'default'  => null),

      'icon'  => array('type'     => 'string',
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

      global $icon;

      switch($state) {

        case DOKU_LEXER_ENTER:

          $type  = $attributes['type'];
          $icon  = $attributes['icon'];
          $color = $attributes['color'];

          $icon_class    = '';
          $text_color    = '';
          $callout_color = '';

          $html_attributes = $this->mergeCoreAttributes($attributes);
          $html_attributes['class'][] = 'bs-wrap bs-callout';

          # Automatic detection of icon
          if (strtolower($icon) == 'true') {

            switch ($type) {

              case 'success':
                $icon_class = 'check-circle';
                break;

              case 'info':
                $icon_class = 'info-circle';
                break;

              case 'danger':
                $icon_class = 'minus-circle';
                break;

              case 'primary':
                $icon_class = 'exclamation-circle';
                break;

              case 'warning':
                $icon_class = 'exclamation-triangle';
                break;

              // Extra
              case 'question':
                $type      = 'primary';
                $icon_class = 'question-circle';
                break;

              case 'tip':
                $type      = 'warning';
                $icon_class = 'lightbulb-o';
                break;

              default:
                $icon_class = $type;

            }

            $icon_class = "fa fa-$icon_class";

          } else {
            $icon_class = $icon;
          }

          if ($color) {
            $html_attributes['style']['border-left-color'] = $color;
            $text_color = sprintf(' style="color:%s"', $color);
          }

          $html_attributes['class'][] = "bs-callout-$type";

          $markup = sprintf('<div %s>', $this->buildAttributes($html_attributes));

          if ($icon && $icon_class) {
            $markup .= sprintf('<div class="row"><div class="col-xs-1"><i class="bs-callout-icon %s"%s></i></div><div class="col-xs-11">', $icon_class, $text_color);
          }

          if ($title = $attributes['title']) {
            $markup .= sprintf('<h4%s>%s</h4>', $text_color, $title);
          }

          $renderer->doc .= $markup;

          return true;

        case DOKU_LEXER_EXIT:

          $markup = '</div>';

          if ($icon) {
            $markup .= '</div></div>';
          }

          $renderer->doc .= $markup;
          return true;

      }

      return true;

    }

}
