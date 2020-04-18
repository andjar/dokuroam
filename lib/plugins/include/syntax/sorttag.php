<?php

if(!defined('DOKU_INC')) die();

/**
 * Include plugin sort order tag, idea and parts of the code copied from the indexmenu plugin.
 *
 * @license     GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author      Samuele Tognini <samuele@netsons.org>
 * @author      Michael Hamann <michael@content-space.de>
 *
 */
class syntax_plugin_include_sorttag extends DokuWiki_Syntax_Plugin {

    /**
     * What kind of syntax are we?
     */
    public function getType(){
        return 'substition';
    }

    /**
     * The paragraph type - block, we don't need paragraph tags
     *
     * @return string The paragraph type
     */
    public function getPType() {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort(){
        return 139;
    }

    /**
     * Connect pattern to lexer
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('{{include_n>.+?}}',$mode,'plugin_include_sorttag');
    }

    /**
     * Handle the match
     */
    public function handle($match, $state, $pos, Doku_Handler $handler){
        $match = substr($match,12,-2);
        return array($match);
    }

    /**
     * Render output
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        if ($mode === 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            $renderer->meta['include_n'] = $data[0];
        }
    }
}
