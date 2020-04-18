<?php
/**
 * Tag Plugin: displays list of keywords with links to categories this page
 * belongs to. The links are marked as tags for Technorati and other services
 * using tagging.
 *
 * Usage: {{tag>category tags space separated}}
 *
 * @license  GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author   Esther Brunner <wikidesign@gmail.com>
 */
 
// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

/**
 * Tag syntax plugin, allows to specify tags in a page
 */
class syntax_plugin_tag_tag extends DokuWiki_Syntax_Plugin {

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
        $this->Lexer->addSpecialPattern('\{\{tag>.*?\}\}', $mode, 'plugin_tag_tag');
    }

    /**
     * Handle matches of the tag syntax
     *
     * @param string $match The match of the syntax
     * @param int    $state The state of the handler
     * @param int    $pos The position in the document
     * @param Doku_Handler    $handler The handler
     * @return array Data for the renderer
     */
    function handle($match, $state, $pos, Doku_Handler $handler) {
        $tags = trim(substr($match, 6, -2));     // strip markup & whitespace
        $tags = preg_replace(array('/[[:blank:]]+/', '/\s+/'), " ", $tags);    // replace linebreaks and multiple spaces with one space character

        if (!$tags) return false;
        
        // load the helper_plugin_tag
        /** @var helper_plugin_tag $my */
        if (!$my = $this->loadHelper('tag')) return false;

        // split tags and returns for renderer
        return $my->_parseTagList($tags);
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
        if ($data === false) return false;
        /** @var helper_plugin_tag $my */
        if (!$my = $this->loadHelper('tag')) return false;

        // XHTML output
        if ($mode == 'xhtml') {
            $tags = $my->tagLinks($data);
            if (!$tags) return true;
            $renderer->doc .= '<div class="'.$this->getConf('tags_list_css').'"><span>'.DOKU_LF.
                DOKU_TAB.$tags.DOKU_LF.
                '</span></div>'.DOKU_LF;
            return true;

        // for metadata renderer
        } elseif ($mode == 'metadata') {
            /** @var Doku_Renderer_metadata $renderer */
            // erase tags on persistent metadata no more used
            if (isset($renderer->persistent['subject'])) {
                unset($renderer->persistent['subject']);
                $renderer->meta['subject'] = array();
            }

            if (!isset($renderer->meta['subject'])) $renderer->meta['subject'] = array();

            // each registered tags in metadata and index should be valid IDs
            $data = array_map('cleanID', $data);
            // merge with previous tags and make the values unique
            $renderer->meta['subject'] = array_unique(array_merge($renderer->meta['subject'], $data));

            if ($renderer->capture) $renderer->doc .= DOKU_LF.implode(' ', $data).DOKU_LF;

            // add references if tag page exists
            foreach ($data as $tag) {
                resolve_pageid($my->namespace, $tag, $exists); // resolve shortcuts
                $renderer->meta['relation']['references'][$tag] = $exists;
            }

            return true;
        }
        return false;
    }
}
// vim:ts=4:sw=4:et: 
