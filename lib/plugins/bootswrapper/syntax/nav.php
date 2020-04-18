<?php
/**
 * Bootstrap Wrapper Plugin: Nav (Pills & Tabs)
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_nav extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<nav.*?>(?=.*?</nav>)';
  public $pattern_end    = '</nav>';
  public $nav_type       = null;
  public $tag_name       = 'nav';
  public $tag_attributes = array(

    'type'      => array('type'     => 'string',
                          'values'   => array('tabs', 'pills'),
                          'required' => true,
                          'default'  => 'pills'),

    'stacked'   => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => false),

    'justified' => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => false),

    'fade' => array('type'     => 'boolean',
                          'values'   => array(0, 1),
                          'required' => false,
                          'default'  => false),

  );

  function getPType() { return 'block'; }

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    /** @var Doku_Renderer_xhtml $renderer */
    list($state, $match, $attributes) = $data;

    switch($state) {

      case DOKU_LEXER_ENTER:

        $html5data  = array();

        if (! empty($this->nav_type)) {
            $attributes['type'] = $this->nav_type;
        }

        foreach ($attributes as $key => $value) {
            $html5data[] = sprintf('data-nav-%s="%s"', $key, $value);
        }

        $markup = sprintf('<div class="bs-wrap bs-wrap-nav" %s>', implode(' ', $html5data));

        $renderer->doc .= $markup;
        return true;

      case DOKU_LEXER_EXIT:
        $renderer->doc .= "</div>";
        return true;

    }

    return true;

  }

}
