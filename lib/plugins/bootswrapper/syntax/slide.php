<?php
/**
 * Bootstrap Wrapper Plugin: Carousel - Slide
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2015, Giuseppe Di Terlizzi
 */
 
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_slide extends syntax_plugin_bootswrapper_bootstrap {

  public $pattern_start  = '<slide>';
  public $pattern_end    = '</slide>';
  public $template_start = '<div class="bs-wrap bs-wrap-slide item">';
  public $template_end   = '</div>';
  public $tag_name       = 'slide';

  function getPType(){ return 'block'; }

}
