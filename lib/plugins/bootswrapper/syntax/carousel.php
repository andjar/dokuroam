<?php
/**
 * Bootstrap Wrapper Plugin: Carousel
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_carousel extends syntax_plugin_bootswrapper_bootstrap {

    public $pattern_start  = '<carousel.*?>(?=.*?</carousel>)';
    public $pattern_end    = '</carousel>';
    public $tag_name       = 'carousel';
    public $tag_attributes = array(

      'interval' => array('type'     => 'integer',
                          'values'   => null,
                          'required' => false,
                          'default'  => 5000),

      'pause'    => array('type'     => 'string',
                          'values'   => null,
                          'required' => false,
                          'default'  => 'hover'),

      'wrap'     => array('type'     => 'boolean',
                          'values'   => null,
                          'required' => false,
                          'default'  => true),

      'keyboard' => array('type'     => 'boolean',
                          'values'   => null,
                          'required' => false,
                          'default'  => true),

    );

    function getPType() { return 'block'; }

    function render($mode, Doku_Renderer $renderer, $data) {

        if (empty($data)) return false;
        if ($mode !== 'xhtml') return false;

        /** @var Doku_Renderer_xhtml $renderer */
        list($state, $match, $attributes) = $data;

        switch($state) {

          case DOKU_LEXER_ENTER:

            $html5_attributes = array();

            foreach ($attributes as $attribute => $value) {
              $html5_attributes[] = sprintf('data-%s="%s"', $attribute, $value);
            }

            $markup = sprintf('<div class="bs-wrap bs-wrap-carousel carousel slide" data-ride="carousel" %s><ol class="carousel-indicators"></ol><div class="carousel-inner" role="listbox">', implode(' ', $html5_attributes));

            $renderer->doc .= $markup;
            return true;

            case DOKU_LEXER_EXIT:

              $renderer->doc .= '</div>
  <a class="left carousel-control" href="#" role="button" data-slide="prev">
    <span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span>
    <span class="sr-only">Previous</span>
  </a>
  <a class="right carousel-control" href="#" role="button" data-slide="next">
    <span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span>
    <span class="sr-only">Next</span>
  </a></div>';
              return true;

        }

        return true;

    }

}
