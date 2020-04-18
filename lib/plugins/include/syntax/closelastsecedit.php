<?php
/**
 * Include plugin (close last section edit)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Hamann <michael@content-space.de>
 */

if (!defined('DOKU_PLUGIN'))
    define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');

class syntax_plugin_include_closelastsecedit extends DokuWiki_Syntax_Plugin {

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
     * Finishes the last open section edit
     */
    function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode == 'xhtml') {
            /** @var Doku_Renderer_xhtml $renderer */
            list($endpos) = $data;
            $renderer->finishSectionEdit($endpos);
            return true;
        }
        return false;
    }
}
// vim:ts=4:sw=4:et:
