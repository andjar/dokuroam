<?php
/**
 * Bootstrap Wrapper Plugin: Useful Macros
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Giuseppe Di Terlizzi <giuseppe.diterlizzi@gmail.com>
 * @copyright  (C) 2016, Giuseppe Di Terlizzi
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(dirname(__FILE__).'/bootstrap.php');

class syntax_plugin_bootswrapper_macros extends DokuWiki_Syntax_Plugin {

  private $macros = array(
    '~~CLEARFIX~~',
    '~~PAGEBREAK~~'
  );

  function getType() { return 'substition'; }
  function getSort() { return 99; }
  function getPType(){ return 'normal'; }

  function connectTo($mode) {

    foreach ($this->macros as $macro) {
      $this->Lexer->addSpecialPattern($macro, $mode, 'plugin_bootswrapper_macros');
    }

  }

  function handle($match, $state, $pos, Doku_Handler $handler) {
    return array($match, $state, $pos);
  }

  function render($mode, Doku_Renderer $renderer, $data) {

    if (empty($data)) return false;
    if ($mode !== 'xhtml') return false;

    list($match, $state, $pos) = $data;

    switch ($match) {
      case '~~CLEARFIX~~':
        $renderer->doc .= '<span class="clearfix"></span>';
        break;
      case '~~PAGEBREAK~~':
        $renderer->doc .= '<span class="bs-page-break"></span>';
        break;
    }

  }

}
