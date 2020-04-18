<?php
/**
 * Include plugin (editbtn header component)
 *
 * @license GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author  Michael Hamann <michael@content-space.de>
 */

class syntax_plugin_include_readmore extends DokuWiki_Syntax_Plugin {

    function getType() {
        return 'formatting';
    }

    function getSort() {
        return 50;
    }

    function handle($match, $state, $pos, Doku_Handler $handler) {
        // this is a syntax plugin that doesn't offer any syntax, so there's nothing to handle by the parser
    }

    function render($mode, Doku_Renderer $renderer, $data) {
        list($page) = $data;

        if ($mode == 'xhtml') {
            $renderer->doc .= DOKU_LF.'<p class="include_readmore">'.DOKU_LF;
        } else {
            $renderer->p_open();
        }

        $renderer->internallink($page, $this->getLang('readmore'));

        if ($mode == 'xhtml') {
            $renderer->doc .= DOKU_LF.'</p>'.DOKU_LF;
        } else {
            $renderer->p_close();
        }

        return true;
    }
}
// vim:ts=4:sw=4:et:
