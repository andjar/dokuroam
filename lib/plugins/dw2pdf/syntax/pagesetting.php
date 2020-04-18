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
class syntax_plugin_dw2pdf_pagesetting extends DokuWiki_Syntax_Plugin {

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
        return 40;
    }

    /**
     * Paragraph Type
     *
     * @see Doku_Handler_Block
     *
     * @return string
     */
    public function getPType() {
        return 'block';
    }

    /**
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~PDF:(?:LANDSCAPE|PORTRAIT)~~', $mode, 'plugin_dw2pdf_pagesetting');
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
        return array($match, $state, $pos);
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
        if($mode == 'xhtml') {
            $orientation = strtolower(substr($data[0], 6, -2));
            $renderer->doc .= "<div class='dw2pdf-$orientation'></div>" . DOKU_LF;
            return true;
        }
        return false;
    }

}