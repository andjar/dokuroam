<?php
/**
 * Bootstrap Wrapper Plugin: Hidden Helper Class
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_hidden extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<hidden>';
  public $pattern_end    = '</hidden>';
  public $template_start = '<div class="bs-wrap bs-wrap-hidden hidden">';
  public $template_end   = '</div>';
  public $tag_name       = 'hidden';

  function getPType(){ return 'block'; }

}
