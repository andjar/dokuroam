<?php
/**
 * Include plugin (wrapper component)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Klier <chi@chimeric.de>
 * @author  Michael Hamann <michael@content-space.de>
 */

if (!defined('DOKU_INC')) die('must be used inside DokuWiki');

class syntax_plugin_include_wrap extends DokuWiki_Syntax_Plugin {

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
     * Wraps the included page in a div and writes section edits for the action component
     * so it can detect where an included page starts/ends.
     *
     * @author Michael Klier <chi@chimeric.de>
     * @author Michael Hamann <michael@content-space.de>
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode == 'xhtml') {
            list($state, $page, $redirect, $secid) = $data;
            switch($state) {
                case 'open':
                    if ($redirect) {
                        if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                            $renderer->startSectionEdit(0, array('target' => 'plugin_include_start', 'name' => $page));
                        } else {
                            $renderer->startSectionEdit(0, 'plugin_include_start', $page);
                        }
                    } else {
                        if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                            $renderer->startSectionEdit(0, array('target' => 'plugin_include_start_noredirect', 'name' => $page));
                        } else {
                            $renderer->startSectionEdit(0, 'plugin_include_start_noredirect', $page);
                        }
                    }
                    $renderer->finishSectionEdit();
                    // Start a new section with type != section so headers in the included page
                    // won't print section edit buttons of the parent page
                    if (defined('SEC_EDIT_PATTERN')) { // for DokuWiki Greebo and more recent versions
                        $renderer->startSectionEdit(0, array('target' => 'plugin_include_end', 'name' => $page));
                    } else {
                        $renderer->startSectionEdit(0, 'plugin_include_end', $page);
                    }
                    if ($secid === NULL) {
                        $id = '';
                    } else {
                        $id = ' id="'.$secid.'"';
                    }
                    $renderer->doc .= '<div class="plugin_include_content plugin_include__' . $page .'"'.$id.'>' . DOKU_LF;
                    if (is_a($renderer,'renderer_plugin_dw2pdf')) {
                        $renderer->doc .= '<a name="'.$secid.'" />';
                    }
                    break;
                case 'close':
                    $renderer->finishSectionEdit();
                    $renderer->doc .= '</div>' . DOKU_LF;
                    break;
            }
            return true;
        }
        return false;
    }
}
// vim:ts=4:sw=4:et:
