<?php
/**
 * Outliner Plugin
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Michael Hamann <michael [at] content-space [dot] de>, Pavel Vitis <pavel [dot] vitis [at] seznam [dot] cz>
 */

if(!defined('DOKU_INC')) define('DOKU_INC',realpath(dirname(__FILE__).'/../../').'/');
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'syntax.php');

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_outliner extends DokuWiki_Syntax_Plugin {
    /**
     * What kind of syntax are we?
     */
    function getType(){
        return 'container';
    }

    function getAllowedTypes() {
        return array('container', 'baseonly', 'formatting', 'substition', 'protected', 'disabled', 'paragraphs');
    }

    /* Accept own mode so nesting is allowed */
    function accepts($mode) {
      if ($mode == substr(get_class($this), 7)) return true;
      return parent::accepts($mode);
    }

    function getPType(){
        return 'block';
    }

    /**
     * Where to sort in?
     */
    function getSort(){
        return 10;
    }

    /**
     * Connect pattern to lexer
     */
    function connectTo($mode) {
        $this->Lexer->addEntryPattern(
            '\n\s*-->[^\n]*(?=.*?\n\s*<--[^\n]*)',
            $mode,
            'plugin_outliner');
    }

    function postConnect() {
        $this->Lexer->addExitPattern(
            '\n\s*<--[^\n]*',
            'plugin_outliner');
    }

    /**
     * Handle the match
     */
    function handle($match, $state, $pos, Doku_Handler $handler){
        global $ID;

        switch ($state) {
        case DOKU_LEXER_ENTER:
            $matches = array();
            preg_match('/-->\s*([^#^]*)([#^]*)/', $match, $matches);
            $title = $matches[1];
            $outline_id = ''.md5($ID).'_'.$pos;

            // Test if '^ - opened node' flag is present
            $opened = (strpos($matches[2], '^') !== false);
            // Test if '# - no popup' flag is present
            $nopopup = (strpos($matches[2], '#') !== false);
            return array($state, $title, $outline_id, $opened, $nopopup);

        case DOKU_LEXER_EXIT:
            return array($state);

        case DOKU_LEXER_UNMATCHED :
            return array($state, $match);

        default:
            return array();
        }
    }

    /**
     * Create output
     */
    function render($mode, Doku_Renderer $renderer, $data) {
      if ($mode == 'xhtml') {
          $state = $data[0];
          switch ($state) {
          case DOKU_LEXER_ENTER:
              list($state, $title, $outline_id, $opened, $nopopup) = $data;
              $renderer->doc .= '<dl class="outliner';
              // only set node id when cookies are used
              if ($this->getConf('usecookie'))
                 $renderer->doc .= ' outl_'.$outline_id;
              if ($opened) $renderer->doc .= ' outliner-open';
              if ($nopopup) $renderer->doc .= ' outliner-nopopup';
              $renderer->doc .= '"><dt>'.hsc($title)."</dt><dd>\n";
              break;
          case DOKU_LEXER_EXIT:
              $renderer->doc .= "</dd></dl>\n";
              break;
          case DOKU_LEXER_UNMATCHED:
              $renderer->cdata($data[1]);
              break;
          }
          return true;
      }
      return false;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
