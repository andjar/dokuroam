<?php
/**
 * Tag Plugin: Display a link to the listing of all pages with a certain tag.
 *
 * Usage: {{tagpage>mytag[&dynamic][|title]}}
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Matthias Schulte <dokuwiki@lupo49.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

if(!defined('DOKU_LF')) define('DOKU_LF', "\n");
if(!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

/** Tagpage syntax, allows to link to a given tag */
class syntax_plugin_tag_tagpage extends DokuWiki_Syntax_Plugin {

    /**
     * @return string Syntax type
     */
    function getType() {
        return 'substition';
    }

    /**
     * @return int Sort order
     */
    function getSort() {
        return 305;
    }

    /**
     * @return string Paragraph type
     */
    function getPType() {
        return 'normal';
    }

    /**
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{tagpage>.*?\}\}', $mode, 'plugin_tag_tagpage');
    }

    /**
     * Handle matches of the count syntax
     *
     * @param string          $match The match of the syntax
     * @param int             $state The state of the handler
     * @param int             $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $params            = array();
        $dump              = trim(substr($match, 10, -2)); // get given tag
        $dump              = explode('|', $dump, 2); // split to tags, link name and options
        $params['title']   = $dump[1];
        $dump              = explode('&', $dump[0]);
        $params['dynamic'] = ($dump[1] == 'dynamic');
        $params['tag']     = trim($dump[0]);

        return $params;
    }

    /**
     * Render xhtml output
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler function
     * @return bool If rendering was successful.
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if($data == false) return false;

        if($mode == "xhtml") {
            if($data['dynamic']) {
                // deactivate (renderer) cache as long as there is no proper cache handling
                // implemented for the count syntax
                $renderer->info['cache'] = false;
            }

            /** @var helper_plugin_tag $my */
            if(!($my = $this->loadHelper('tag'))) return false;

            $renderer->doc .= $my->tagLink($data['tag'], $data['title'], $data['dynamic']);
            return true;
        }
        return false;
    }
}
// vim:ts=4:sw=4:et: 
