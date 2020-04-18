<?php
/**
 * Tag Plugin: displays list of keywords with links to categories this page
 * belongs to. The links are marked as tags for Technorati and other services
 * using tagging.
 *
 * Usage: {{tag>category tags space separated}}
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Matthias Schulte <dokuwiki@lupo49.de>
 */
 
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

/** Count syntax, allows to list tag counts */
class syntax_plugin_tag_count extends DokuWiki_Syntax_Plugin {

    /**
     * @return string Syntax type
     */
    function getType() { return 'substition'; }
    /**
     * @return int Sort order
     */
    function getSort() { return 305; }
    /**
     * @return string Paragraph type
     */
    function getPType() { return 'block';}

    /**
     * @param string $mode Parser mode
     */
    function connectTo($mode) {
        $this->Lexer->addSpecialPattern('\{\{count>.*?\}\}', $mode, 'plugin_tag_count');
    }

    /**
     * Handle matches of the count syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {

        $dump = trim(substr($match, 8, -2));     // get given tags
        $dump = explode('&', $dump);             // split to tags and allowed namespaces 
        $tags = $dump[0];
        $allowedNamespaces = explode(' ', $dump[1]); // split given namespaces into an array

        if($allowedNamespaces && $allowedNamespaces[0] == '') {
            unset($allowedNamespaces[0]);    // When exists, remove leading space after the delimiter
            $allowedNamespaces = array_values($allowedNamespaces);
        }

        if (empty($allowedNamespaces)) $allowedNamespaces = null;

        if (!$tags) $tags = '+';

        /** @var helper_plugin_tag $my */
        if(!($my = $this->loadHelper('tag'))) return false;

        return array($my->_parseTagList($tags), $allowedNamespaces);
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string         $mode      Renderer mode (supported modes: xhtml and metadata)
     * @param Doku_Renderer  $renderer  The renderer
     * @param array          $data      The data from the handler function
     * @return bool If rendering was successful.
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if ($data == false) return false;

        list($tags, $allowedNamespaces) = $data;

        // deactivate (renderer) cache as long as there is no proper cache handling
        // implemented for the count syntax
        $renderer->info['cache'] = false;

        if($mode == "xhtml") {
            /** @var helper_plugin_tag $my */
            if(!($my = $this->loadHelper('tag'))) return false;

            // get tags and their occurrences
            if($tags[0] == '+') {
                // no tags given, list all tags for allowed namespaces
                $occurrences = $my->tagOccurrences($tags, $allowedNamespaces, true);
            } else {
                $occurrences = $my->tagOccurrences($tags, $allowedNamespaces);
            }

            $class = "inline"; // valid: inline, ul, pagelist
            $col = "page";

            $renderer->doc .= '<table class="'.$class.'">'.DOKU_LF;
            $renderer->doc .= DOKU_TAB.'<tr>'.DOKU_LF.DOKU_TAB.DOKU_TAB;
            $renderer->doc .= '<th class="'.$col.'">'.$this->getLang('tag').'</th>';
            $renderer->doc .= '<th class="'.$col.'">'.$this->getLang('count').'</th>';
            $renderer->doc .= DOKU_LF.DOKU_TAB.'</tr>'.DOKU_LF;

            if(empty($occurrences)) {
                // Skip output
                $renderer->doc .= DOKU_TAB.'<tr>'.DOKU_LF.DOKU_TAB.DOKU_TAB;
                $renderer->doc .= DOKU_TAB.DOKU_TAB.'<td class="'.$class.'" colspan="2">'.$this->getLang('empty_output').'</td>'.DOKU_LF;
                $renderer->doc .= DOKU_LF.DOKU_TAB.'</tr>'.DOKU_LF;
            } else {
                foreach($occurrences as $tagname => $count) {
                    if($count <= 0) continue; // don't display tags with zero occurrences
                    $renderer->doc .= DOKU_TAB.'<tr>'.DOKU_LF.DOKU_TAB.DOKU_TAB;
                    $renderer->doc .= DOKU_TAB.DOKU_TAB.'<td class="'.$class.'">'.$my->tagLink($tagname).'</td>'.DOKU_LF;
                    $renderer->doc .= DOKU_TAB.DOKU_TAB.'<td class="'.$class.'">'.$count.'</td>'.DOKU_LF;
                    $renderer->doc .= DOKU_LF.DOKU_TAB.'</tr>'.DOKU_LF;
                }
            }
            $renderer->doc .= '</table>'.DOKU_LF;
        }
        return true;
    }
}
// vim:ts=4:sw=4:et: 
