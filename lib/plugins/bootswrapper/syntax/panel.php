<?php
/**
 * Bootstrap Wrapper Plugin: Panel
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_panel extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<panel.*?>(?=.*?</panel>)';
  public $pattern_end    = '</panel>';
  public $tag_name       = 'panel';
  public $tag_attributes = array(

    'type'      => array('type'     => 'string',
                          'values'   => array('default', 'primary', 'success', 'info', 'warning', 'danger'),
                          'required' => true,
                          'default'  => 'default'),

    'title'     => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'footer'    => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'subtitle'  => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'icon'      => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => null),

    'no-body'   => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => false),

  );


  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

        /** @var Doku_Renderer_xhtml $renderer */
        list($state, $match, $attributes) = $data;

        global $nobody, $footer;

        switch($state) {

          case DOKU_LEXER_ENTER:

            $type     = $attributes['type'];
            $title    = $attributes['title'];
            $footer   = $attributes['footer'];
            $subtitle = $attributes['subtitle'];
            $icon     = $attributes['icon'];
            $nobody   = $attributes['no-body'];

            $markup = sprintf('<div class="bs-wrap bs-wrap-panel panel panel-%s">', $type);

            if ($title || $subtitle) {

                if ($icon) {
                  $title = sprintf('<i class="%s"></i> %s', $icon, $title);
                }

                $markup .= sprintf('<div class="panel-heading"><h4 class="panel-title">%s</h4>%s</div>', $title, $subtitle);

            }

            if (! $nobody) {
              $markup .= '<div class="panel-body">';
            }

            $renderer->doc .= $markup;

            return true;

          case DOKU_LEXER_EXIT:

            if (! $nobody) {
              $markup = '</div>';
            }

            if ($footer) {
                $markup .= sprintf('<div class="panel-footer">%s</div>', $footer);
            }

            $markup .= '</div>';

            $renderer->doc .= $markup;

            return true;

        }

        return true;

    }

}
