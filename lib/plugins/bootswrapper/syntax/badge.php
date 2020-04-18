<?php
/**
 * Bootstrap Wrapper Plugin: Badge
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015-2016, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_badge extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start = '<badge>';
  public $pattern_end   = '</badge>';
  public $template_start = '<span class="bs-wrap bs-wrap-badge badge">';
  public $template_end   = '</span>';
  public $tag_name       = 'badge';

  function getPType() { return 'normal'; }

}
