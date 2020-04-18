<?php
/**
 * Include plugin (permalink header component)
 *
 * Provides a header instruction which renders a permalink to the included page
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Gina Haeussge <osd@foosel.net>
 * @author  Michael Klier <chi@chimeric.de>
 */

if (!defined('DOKU_INC'))
    define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . '/');
if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
require_once (DOKU_PLUGIN . 'syntax.php');

class syntax_plugin_include_header extends DokuWiki_Syntax_Plugin {

    function getType() {
        return 'formatting';
    }
    
    function getSort() {
        return 50;
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        // this is a syntax plugin that doesn't offer any syntax, so there's nothing to handle by the parser
    }

    /**
     * Renders a permalink header.
     * 
     * Code heavily copied from the header renderer from inc/parser/xhtml.php, just
     * added an href parameter to the anchor tag linking to the wikilink.
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        global $conf;

        list($headline, $lvl, $pos, $page, $sect, $flags) = $data;

        if ($mode == 'xhtml') {
            /** @var Doku_Renderer_xhtml $renderer */
            $hid = $renderer->_headerToLink($headline, true);
            $renderer->toc_additem($hid, $headline, $lvl);
            $url = ($sect) ? wl($page) . '#' . $sect : wl($page);
            $renderer->doc .= DOKU_LF.'<h' . $lvl;
            $classes = array();
            if($flags['taglogos']) {
                $tag = $this->_get_firsttag($page);
                if($tag) {
                    $classes[] = 'include_firsttag__' . $tag;
                }
            }
            // the include header instruction is always at the beginning of the first section edit inside the include
            // wrap so there is no need to close a previous section edit.
            if ($lvl <= $conf['maxseclevel']) {
                if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                    $classes[] = $renderer->startSectionEdit($pos, array('target' => 'section', 'name' => $headline, 'hid' => $hid));
                } else {
                    $classes[] = $renderer->startSectionEdit($pos, 'section', $headline);
                }
            }
            if ($classes) {
                $renderer->doc .= ' class="'. implode(' ', $classes) . '"';
            }
            $headline = $renderer->_xmlEntities($headline);
            $renderer->doc .= ' id="'.$hid.'"><a href="' . $url . '" title="' . $headline . '">';
            $renderer->doc .= $headline;
            $renderer->doc .= '</a></h' . $lvl . '>' . DOKU_LF;
            return true;
        } else {
            $renderer->header($headline, $lvl, $pos);
        }
        return false;
    }

    /**
     * Optionally add a CSS class for the first tag
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _get_firsttag($page) {
        if(plugin_isdisabled('tag') || (!$taghelper =& plugin_load('helper', 'tag'))) {
            return false;
        }
        $subject = p_get_metadata($page, 'subject');
        if (is_array($subject)) {
            $tag = $subject[0];
        } else {
            list($tag, $rest) = explode(' ', $subject, 2);
        }
        if($tag) {
            return $tag;
        } else {
            return false;
        }
    }
}
// vim:ts=4:sw=4:et:
