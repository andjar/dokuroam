<?php
/**
 * Bootstrap Wrapper Plugin: Show Helper Class
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_show extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<show>';
  public $pattern_end    = '</show>';
  public $template_start = '<div class="bs-wrap bs-wrap-show show">';
  public $template_end   = '</div>';
  public $tag_name       = 'show';

  function getPType(){ return 'block'; }

}
