<?php
/**
 * Bootstrap Wrapper Plugin: Collapse
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_collapse extends syntax_plugin_bootswrapper_bootstrap {

    public $pattern_start  = '<collapse.*?>(?=.*?</collapse>)';
    public $pattern_end    = '</collapse>';
    public $tag_name       = 'collapse';
    public $tag_attributes = array(

      'id'        => array('type'     => 'string',
                           'values'   => null,
                           'required' => true,
                           'default'  => null),

      'collapsed' => array('type'     => 'boolean',
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

            $id        = $attributes['id'];
            $collapsed = $attributes['collapsed'];
            $markup    = sprintf('<div class="bs-wrap bs-wrap-collapse collapse %s" id="%s">', ($collapsed ? '' : 'in'), $id);

            $renderer->doc .= $markup;
            return true;

          case DOKU_LEXER_EXIT:
            $renderer->doc .= '</div>';
            return true;

        }

        return true;

    }

}
