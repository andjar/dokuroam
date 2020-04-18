<?php
/**
 * DokuWiki Plugin dw2pdf (Syntax Component)
 *
 * For marking changes in page orientation.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Sam Wilson <sam@samwilson.id.au>
 */
/* Must be run within Dokuwiki */
if(!defined('DOKU_INC')) die();

/**
 * Syntax for page specific directions for mpdf library
 */
class syntax_plugin_dw2pdf_exportlink extends DokuWiki_Syntax_Plugin {

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     *
     * @return string
     */
    public function getType() {
        return 'substition';
    }

    /**
     * Sort for applying this mode
     *
     * @return int
     */
    public function getSort() {
        return 41;
    }

    /**
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~PDFNS>(?:.*?)\|(?:.*?)~~', $mode, 'plugin_dw2pdf_exportlink');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        global $ID;
        $ns = substr($match,8,strpos($match,'|')-8);
        $id = $ns . ':start';
        resolve_pageid(getNS($ID),$id,$exists);
        $ns = getNS($id);
        $title = substr($match,strpos($match,'|')+1,-2);
        $link = '?do=export_pdfns&book_ns=' . $ns . '&book_title=' . $title;

        // check if there is an ampersand in the title
        $amp = strpos($title,'&');
        if ($amp !== false) {
            $title = substr($title,0,$amp);
        }

        return array('link' => $link, 'title' => sprintf($this->getLang('export_ns'),$ns,$title),$state, $pos);
    }

    /**
     * Handles the actual output creation.
     *
     * @param string        $mode     output format being rendered
     * @param Doku_Renderer $renderer the current renderer object
     * @param array         $data     data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        if($mode == 'xhtml' && !is_a($renderer,'renderer_plugin_dw2pdf')) {
            $renderer->internallink($data['link'],$data['title']);
            return true;
        }
        return false;
    }

}
