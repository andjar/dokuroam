<?php
/**
 * DokuWiki Plugin dw2pdf (Renderer Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <gohr@cosmocode.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Render xhtml suitable as input for mpdf library
 */
class renderer_plugin_dw2pdf extends Doku_Renderer_xhtml {

    private $lastHeaderLevel = -1;
    private $originalHeaderLevel = 0;
    private $difference = 0;

    /**
     * Stores action instance
     *
     * @var action_plugin_dw2pdf
     */
    private $actioninstance = null;

    /**
     * load action plugin instance
     */
    public function __construct() {
        $this->actioninstance = plugin_load('action', 'dw2pdf');
    }

    public function document_start() {
        global $ID;

        parent::document_start();

        //ancher for rewritten links to included pages
        $check = false;
        $pid = sectionID($ID, $check);

        $this->doc .= "<a name=\"{$pid}__\">";
        $this->doc .= "</a>";
    }

    /**
     * Make available as XHTML replacement renderer
     *
     * @param $format
     * @return bool
     */
    public function canRender($format) {
        if($format == 'xhtml') return true;
        return false;
    }

    /**
     * Simplified header printing with PDF bookmarks
     *
     * @param string $text
     * @param int $level from 1 (highest) to 6 (lowest)
     * @param int $pos
     */
    public function header($text, $level, $pos) {
        if(!$text) return; //skip empty headlines
        global $ID;

        $hid = $this->_headerToLink($text, true);

        //only add items within global configured levels (doesn't check the pdf toc settings)
        $this->toc_additem($hid, $text, $level);

        $check = false;
        $pid = sectionID($ID, $check);
        $hid = $pid . '__' . $hid;

        // add PDF bookmark
        $bookmark = '';
        $maxbookmarklevel = $this->actioninstance->getExportConfig('maxbookmarks');
        // 0: off, 1-6: show down to this level
        if($maxbookmarklevel && $maxbookmarklevel >= $level) {
            $bookmarklevel = $this->calculateBookmarklevel($level);
            $bookmark = '<bookmark content="' . $this->_xmlEntities($text) . '" level="' . ($bookmarklevel) . '" />';
        }

        // print header
        $this->doc .= DOKU_LF . "<h$level>$bookmark";
        $this->doc .= "<a name=\"$hid\">";
        $this->doc .= $this->_xmlEntities($text);
        $this->doc .= "</a>";
        $this->doc .= "</h$level>" . DOKU_LF;
    }

    /**
     * Bookmark levels might increase maximal +1 per level.
     * (note: levels start at 1, bookmarklevels at 0)
     *
     * @param int $level 1 (highest) to 6 (lowest)
     * @return int
     */
    protected function calculateBookmarklevel($level) {
        if($this->lastHeaderLevel == -1) {
            $this->lastHeaderLevel = $level;
        }
        $step = $level - $this->lastHeaderLevel;
        if($step > 1) {
            $this->difference = $this->difference + ($step - 1);
        }
        if($step < 0) {
            $this->difference = min($this->difference, $level - $this->originalHeaderLevel);
            $this->difference = max($this->difference, 0);
        }

        $bookmarklevel = $level - $this->difference;

        if($step > 1) {
            $this->originalHeaderLevel = $bookmarklevel;
        }

        $this->lastHeaderLevel = $level;
        return $bookmarklevel - 1; //zero indexed
    }

    /**
     * Render a page local link
     *
     * // modified copy of parent function
     *
     * @param string $hash hash link identifier
     * @param string $name name for the link
     * @param bool $returnonly
     * @return string|void
     *
     * @see Doku_Renderer_xhtml::locallink
     */
    function locallink($hash, $name = null, $returnonly = false) {
        global $ID;
        $name = $this->_getLinkTitle($name, $hash, $isImage);
        $hash = $this->_headerToLink($hash);
        $title = $ID . ' ↵';

        $check = false;
        $pid = sectionID($ID, $check);

        $this->doc .= '<a href="#' . $pid . '__' . $hash . '" title="' . $title . '" class="wikilink1">';
        $this->doc .= $name;
        $this->doc .= '</a>';
    }

    /**
     * Wrap centered media in a div to center it
     *
     * @param string $src       media ID
     * @param string $title     descriptive text
     * @param string $align     left|center|right
     * @param int    $width     width of media in pixel
     * @param int    $height    height of media in pixel
     * @param string $cache     cache|recache|nocache
     * @param bool   $render    should the media be embedded inline or just linked
     * @return string
     */
    function _media($src, $title = NULL, $align = NULL, $width = NULL,
                    $height = NULL, $cache = NULL, $render = true) {

        $out = '';
        if($align == 'center') {
            $out .= '<div align="center" style="text-align: center">';
        }

        $out .= parent::_media($src, $title, $align, $width, $height, $cache, $render);

        if($align == 'center') {
            $out .= '</div>';
        }

        return $out;
    }

    /**
     * hover info makes no sense in PDFs, so drop acronyms
     *
     * @param string $acronym
     */
    function acronym($acronym) {
        $this->doc .= $this->_xmlEntities($acronym);
    }

    /**
     * reformat links if needed
     *
     * @param array $link
     * @return string
     */
    function _formatLink($link) {

        // for internal links contains the title the pageid
        if(in_array($link['title'], $this->actioninstance->getExportedPages())) {
            list(/* $url */, $hash) = explode('#', $link['url'], 2);

            $check = false;
            $pid = sectionID($link['title'], $check);
            $link['url'] = "#" . $pid . '__' . $hash;
        }

        // prefix interwiki links with interwiki icon
        if($link['name'][0] != '<' && preg_match('/\binterwiki iw_(.\w+)\b/', $link['class'], $m)) {
            if(file_exists(DOKU_INC . 'lib/images/interwiki/' . $m[1] . '.png')) {
                $img = DOKU_BASE . 'lib/images/interwiki/' . $m[1] . '.png';
            } elseif(file_exists(DOKU_INC . 'lib/images/interwiki/' . $m[1] . '.gif')) {
                $img = DOKU_BASE . 'lib/images/interwiki/' . $m[1] . '.gif';
            } else {
                $img = DOKU_BASE . 'lib/images/interwiki.png';
            }

            $link['name'] = '<img src="' . $img . '" width="16" height="16" style="vertical-align: center" class="' . $link['class'] . '" />' . $link['name'];
        }
        return parent::_formatLink($link);
    }

    /**
     * no obfuscation for email addresses
     *
     * @param string $address
     * @param null $name
     * @param bool $returnonly
     * @return string|void
     */
    function emaillink($address, $name = NULL, $returnonly = false) {
        global $conf;
        $old = $conf['mailguard'];
        $conf['mailguard'] = 'none';
        parent::emaillink($address, $name, $returnonly);
        $conf['mailguard'] = $old;
    }

}

