<?php
/**
 * dw2Pdf Plugin: Conversion from dokuwiki content to pdf.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Luigi Micco <l.micco@tiscali.it>
 * @author     Andreas Gohr <andi@splitbrain.org>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class action_plugin_dw2pdf
 *
 * Export hmtl content to pdf, for different url parameter configurations
 * DokuPDF which extends mPDF is used for generating the pdf from html.
 */
class action_plugin_dw2pdf extends DokuWiki_Action_Plugin {
    /**
     * Settings for current export, collected from url param, plugin config, global config
     *
     * @var array
     */
    protected $exportConfig = null;
    protected $tpl;
    protected $title;
    protected $list = array();
    protected $onetimefile = false;

    /**
     * Constructor. Sets the correct template
     *
     * @param string $title
     */
    public function __construct($title=null) {
        $this->tpl   = $this->getExportConfig('template');
        $this->title = $title ? $title : '';
    }

    /**
     * Delete cached files that were for one-time use
     */
    public function __destruct() {
        if($this->onetimefile) {
            unlink($this->onetimefile);
        }
    }

    /**
     * Register the events
     *
     * @param Doku_Event_Handler $controller
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'convert', array());
        $controller->register_hook('TEMPLATE_PAGETOOLS_DISPLAY', 'BEFORE', $this, 'addbutton', array());
        $controller->register_hook('MENU_ITEMS_ASSEMBLY', 'AFTER', $this, 'addsvgbutton', array());
    }

    /**
     * Do the HTML to PDF conversion work
     *
     * @param Doku_Event $event
     */
    public function convert(Doku_Event $event) {
        global $ID, $REV, $DATE_AT;
        global $conf, $INPUT;

        // our event?
        if(($event->data != 'export_pdfbook') && ($event->data != 'export_pdf') && ($event->data != 'export_pdfns')) return;

        // check user's rights
        if(auth_quickaclcheck($ID) < AUTH_READ) return;

        if($data = $this->collectExportPages($event)) {
            list($this->title, $this->list) = $data;
        } else {
            return;
        }

        if($event->data === 'export_pdf' && ($REV || $DATE_AT)) {
            $cachefile = tempnam($conf['tmpdir'] . '/dwpdf', 'dw2pdf_');
            $this->onetimefile = $cachefile;
            $generateNewPdf = true;
        } else {
            // prepare cache and its dependencies
            $depends = array();
            $cache = $this->prepareCache($depends);
            $cachefile = $cache->cache;
            $generateNewPdf = !$this->getConf('usecache')
                || $this->getExportConfig('isDebug')
                || !$cache->useCache($depends);
        }

        // hard work only when no cache available or needed for debugging
        if($generateNewPdf) {
            // generating the pdf may take a long time for larger wikis / namespaces with many pages
            set_time_limit(0);
            try {
                $this->generatePDF($cachefile, $event);
            } catch(Mpdf\MpdfException $e) {
                if($INPUT->has('selection')) {
                    http_status(400);
                    print $e->getMessage();
                    exit();
                } else {
                    //prevent act_export()
                    $event->data = 'show';
                    msg($e->getMessage(), -1);
                    $_SERVER['REQUEST_METHOD'] = 'POST'; //clears url
                    return;
                }
            }
        }

        $event->preventDefault(); // after prevent, $event->data cannot be changed

        // deliver the file
        $this->sendPDFFile($cachefile);  //exits
    }

    /**
     * Obtain list of pages and title, based on url parameters
     *
     * @param Doku_Event $event
     * @return string|bool
     */
    protected function collectExportPages(Doku_Event $event) {
        global $ID, $REV;
        global $INPUT;
        global $conf;

        // list of one or multiple pages
        $list = array();

        if($event->data == 'export_pdf') {
            $list[0] = $ID;
            $this->title = $INPUT->str('pdftitle'); //DEPRECATED
            $this->title = $INPUT->str('book_title', $this->title, true);
            if(empty($this->title)) {
                $this->title = p_get_first_heading($ID);
            }

            $filename = wikiFN($ID, $REV);
            if(!file_exists($filename)) {
                $this->showPageWithErrorMsg($event, 'notexist');
                return false;
            }

        } elseif($event->data == 'export_pdfns') {
            //check input for title and ns
            if(!$this->title = $INPUT->str('book_title')) {
                $this->showPageWithErrorMsg($event, 'needtitle');
                return false;
            }
            $pdfnamespace = cleanID($INPUT->str('book_ns'));
            if(!@is_dir(dirname(wikiFN($pdfnamespace . ':dummy')))) {
                $this->showPageWithErrorMsg($event, 'needns');
                return false;
            }

            //sort order
            $order = $INPUT->str('book_order', 'natural', true);
            $sortoptions = array('pagename', 'date', 'natural');
            if(!in_array($order, $sortoptions)) {
                $order = 'natural';
            }

            //search depth
            $depth = $INPUT->int('book_nsdepth', 0);
            if($depth < 0) {
                $depth = 0;
            }

            //page search
            $result = array();
            $opts = array('depth' => $depth); //recursive all levels
            $dir = utf8_encodeFN(str_replace(':', '/', $pdfnamespace));
            search($result, $conf['datadir'], 'search_allpages', $opts, $dir);

            // exclude ids
            $excludes = $INPUT->arr('excludes');
            if (!empty($excludes)) {
                $result = array_filter($result, function ($item) use ($excludes) {
                    return array_search($item['id'], $excludes) === false;
                });
            }

            //sorting
            if(count($result) > 0) {
                if($order == 'date') {
                    usort($result, array($this, '_datesort'));
                } elseif($order == 'pagename') {
                    usort($result, array($this, '_pagenamesort'));
                }
            }

            foreach($result as $item) {
                $list[] = $item['id'];
            }

            if ($pdfnamespace !== '') {
                if (!in_array($pdfnamespace . ':' . $conf['start'], $list, true)) {
                    if (file_exists(wikiFN(rtrim($pdfnamespace,':')))) {
                        array_unshift($list,rtrim($pdfnamespace,':'));
                    }
                }
            }

        } elseif(isset($_COOKIE['list-pagelist']) && !empty($_COOKIE['list-pagelist'])) {
            /** @deprecated  April 2016 replaced by localStorage version of Bookcreator*/
            //is in Bookmanager of bookcreator plugin a title given?
            $this->title = $INPUT->str('pdfbook_title'); //DEPRECATED
            $this->title = $INPUT->str('book_title', $this->title, true);
            if(empty($this->title)) {
                $this->showPageWithErrorMsg($event, 'needtitle');
                return false;
            } else {
                $list = explode("|", $_COOKIE['list-pagelist']);
            }

        } elseif($INPUT->has('selection')) {
            //handle Bookcreator requests based at localStorage
//            if(!checkSecurityToken()) {
//                http_status(403);
//                print $this->getLang('empty');
//                exit();
//            }

            $json = new JSON(JSON_LOOSE_TYPE);
            $list = $json->decode($INPUT->post->str('selection', '', true));
            if(!is_array($list) || empty($list)) {
                http_status(400);
                print $this->getLang('empty');
                exit();
            }

            $this->title = $INPUT->str('pdfbook_title'); //DEPRECATED
            $this->title = $INPUT->str('book_title', $this->title, true);
            if(empty($this->title)) {
                http_status(400);
                print $this->getLang('needtitle');
                exit();
            }

        } else {
            //show empty bookcreator message
            $this->showPageWithErrorMsg($event, 'empty');
            return false;
        }

        $list = array_map('cleanID', $list);

        $skippedpages = array();
        foreach($list as $index => $pageid) {
            if(auth_quickaclcheck($pageid) < AUTH_READ) {
                $skippedpages[] = $pageid;
                unset($list[$index]);
            }
        }
        $list = array_filter($list); //removes also pages mentioned '0'

        //if selection contains forbidden pages throw (overridable) warning
        if(!$INPUT->bool('book_skipforbiddenpages') && !empty($skippedpages)) {
            $msg = hsc(join(', ', $skippedpages));
            if($INPUT->has('selection')) {
                http_status(400);
                print sprintf($this->getLang('forbidden'), $msg);
                exit();
            } else {
                $this->showPageWithErrorMsg($event, 'forbidden', $msg);
                return false;
            }

        }

        return array($this->title, $list);
    }

    /**
     * Prepare cache
     *
     * @param array  $depends (reference) array with dependencies
     * @return cache
     */
    protected function prepareCache(&$depends) {
        global $REV;

        $cachekey = join(',', $this->list)
            . $REV
            . $this->getExportConfig('template')
            . $this->getExportConfig('pagesize')
            . $this->getExportConfig('orientation')
            . $this->getExportConfig('font-size')
            . $this->getExportConfig('doublesided')
            . ($this->getExportConfig('hasToC') ? join('-', $this->getExportConfig('levels')) : '0')
            . $this->title;
        $cache = new cache($cachekey, '.dw2.pdf');

        $dependencies = array();
        foreach($this->list as $pageid) {
            $relations = p_get_metadata($pageid, 'relation');

            if(is_array($relations)) {
                if(array_key_exists('media', $relations) && is_array($relations['media'])) {
                    foreach($relations['media'] as $mediaid => $exists) {
                        if($exists) {
                            $dependencies[] = mediaFN($mediaid);
                        }
                    }
                }

                if(array_key_exists('haspart', $relations) && is_array($relations['haspart'])) {
                    foreach($relations['haspart'] as $part_pageid => $exists) {
                        if($exists) {
                            $dependencies[] = wikiFN($part_pageid);
                        }
                    }
                }
            }

            $dependencies[] = metaFN($pageid, '.meta');
        }

        $depends['files'] = array_map('wikiFN', $this->list);
        $depends['files'][] = __FILE__;
        $depends['files'][] = dirname(__FILE__) . '/renderer.php';
        $depends['files'][] = dirname(__FILE__) . '/mpdf/mpdf.php';
        $depends['files'] = array_merge(
            $depends['files'],
            $dependencies,
            getConfigFiles('main')
        );
        return $cache;
    }

    /**
     * Set error notification and reload page again
     *
     * @param Doku_Event $event
     * @param string $msglangkey key of translation key
     * @param string $replacement
     */
    private function showPageWithErrorMsg(Doku_Event $event, $msglangkey, $replacement=null) {
        if(empty($replacement)) {
            $msg = $this->getLang($msglangkey);
        } else {
            $msg = sprintf($this->getLang($msglangkey), $replacement);
        }
        msg($msg, -1);

        $event->data = 'show';
        $_SERVER['REQUEST_METHOD'] = 'POST'; //clears url
    }

    /**
     * Returns the parsed Wikitext in dw2pdf for the given id and revision
     *
     * @param string     $id  page id
     * @param string|int $rev revision timestamp or empty string
     * @param string     $date_at
     * @return null|string
     */
    protected function p_wiki_dw2pdf($id, $rev = '', $date_at = '') {
        $file = wikiFN($id, $rev);

        if(!file_exists($file)) return '';

        //ensure $id is in global $ID (needed for parsing)
        global $ID;
        $keep = $ID;
        $ID   = $id;

        if($rev || $date_at) {
            $ret = p_render('dw2pdf', p_get_instructions(io_readWikiPage($file, $id, $rev)), $info, $date_at); //no caching on old revisions
        } else {
            $ret = p_cached_output($file, 'dw2pdf', $id);
        }

        //restore ID (just in case)
        $ID = $keep;

        return $ret;
    }

    /**
     * Build a pdf from the html
     *
     * @param string $cachefile
     * @param Doku_Event $event
     */
    protected function generatePDF($cachefile, $event) {
        global $REV, $INPUT, $DATE_AT;

        if ($event->data == 'export_pdf') { //only one page is exported
            $rev = $REV;
            $date_at = $DATE_AT;
        } else { //we are exporting entre namespace, ommit revisions
            $rev = $date_at = '';
        }

        //some shortcuts to export settings
        $hasToC = $this->getExportConfig('hasToC');
        $levels = $this->getExportConfig('levels');
        $isDebug = $this->getExportConfig('isDebug');

        // initialize PDF library
        require_once(dirname(__FILE__) . "/DokuPDF.class.php");

        $mpdf = new DokuPDF($this->getExportConfig('pagesize'),
                            $this->getExportConfig('orientation'),
                            $this->getExportConfig('font-size'));

        // let mpdf fix local links
        $self = parse_url(DOKU_URL);
        $url = $self['scheme'] . '://' . $self['host'];
        if($self['port']) {
            $url .= ':' . $self['port'];
        }
        $mpdf->SetBasePath($url);

        // Set the title
        $mpdf->SetTitle($this->title);

        // some default document settings
        //note: double-sided document, starts at an odd page (first page is a right-hand side page)
        //      single-side document has only odd pages
        $mpdf->mirrorMargins = $this->getExportConfig('doublesided');
        $mpdf->setAutoTopMargin = 'stretch';
        $mpdf->setAutoBottomMargin = 'stretch';
//            $mpdf->pagenumSuffix = '/'; //prefix for {nbpg}
        if($hasToC) {
            $mpdf->PageNumSubstitutions[] = array('from' => 1, 'reset' => 0, 'type' => 'i', 'suppress' => 'off'); //use italic pageno until ToC
            $mpdf->h2toc = $levels;
        } else {
            $mpdf->PageNumSubstitutions[] = array('from' => 1, 'reset' => 0, 'type' => '1', 'suppress' => 'off');
        }

        // load the template
        $template = $this->load_template();

        // prepare HTML header styles
        $html = '';
        if($isDebug) {
            $html .= '<html><head>';
            $html .= '<style type="text/css">';
        }

        $styles = '@page { size:auto; ' . $template['page'] . '}';
        $styles .= '@page :first {' . $template['first'] . '}';

        $styles .= '@page landscape-page { size:landscape }';
        $styles .= 'div.dw2pdf-landscape { page:landscape-page }';
        $styles .= '@page portrait-page { size:portrait }';
        $styles .= 'div.dw2pdf-portrait { page:portrait-page }';
        $styles .= $this->load_css();

        $mpdf->WriteHTML($styles, 1);

        if($isDebug) {
            $html .= $styles;
            $html .= '</style>';
            $html .= '</head><body>';
        }

        $body_start = $template['html'];
        $body_start .= '<div class="dokuwiki">';

        // insert the cover page
        $body_start .= $template['cover'];

        $mpdf->WriteHTML($body_start, 2, true, false); //start body html
        if($isDebug) {
            $html .= $body_start;
        }
        if($hasToC) {
            //Note: - for double-sided document the ToC is always on an even number of pages, so that the following content is on a correct odd/even page
            //      - first page of ToC starts always at odd page (so eventually an additional blank page is included before)
            //      - there is no page numbering at the pages of the ToC
            $mpdf->TOCpagebreakByArray(
                array(
                    'toc-preHTML' => '<h2>' . $this->getLang('tocheader') . '</h2>',
                    'toc-bookmarkText' => $this->getLang('tocheader'),
                    'links' => true,
                    'outdent' => '1em',
                    'resetpagenum' => true, //start pagenumbering after ToC
                    'pagenumstyle' => '1'
                )
            );
            $html .= '<tocpagebreak>';
        }

        // loop over all pages
        $counter = 0;
        $no_pages = count($this->list);
        foreach($this->list as $page) {
            $counter++;

            $pagehtml = $this->p_wiki_dw2pdf($page, $rev, $date_at);
            //file doesn't exists
            if($pagehtml == '') {
                continue;
            }
            $pagehtml .= $this->page_depend_replacements($template['cite'], $page);
            if($counter < $no_pages) {
                $pagehtml .= '<pagebreak />';
            }

            $mpdf->WriteHTML($pagehtml, 2, false, false); //intermediate body html
            if($isDebug) {
                $html .= $pagehtml;
            }
        }

        // insert the back page
        $body_end = $template['back'];

        $body_end .= '</div>';

        $mpdf->WriteHTML($body_end, 2, false, true); // finish body html
        if($isDebug) {
            $html .= $body_end;
            $html .= '</body>';
            $html .= '</html>';
        }

        //Return html for debugging
        if($isDebug) {
            if($INPUT->str('debughtml', 'text', true) == 'html') {
                echo $html;
            } else {
                header('Content-Type: text/plain; charset=utf-8');
                echo $html;
            }
            exit();
        };

        // write to cache file
        $mpdf->Output($cachefile, 'F');
    }

    /**
     * @param string $cachefile
     */
    protected function sendPDFFile($cachefile) {
        header('Content-Type: application/pdf');
        header('Cache-Control: must-revalidate, no-transform, post-check=0, pre-check=0');
        header('Pragma: public');
        http_conditionalRequest(filemtime($cachefile));
        global $INPUT;
        $outputTarget = $INPUT->str('outputTarget', $this->getConf('output'));

        $filename = rawurlencode(cleanID(strtr($this->title, ':/;"', '    ')));
        if($outputTarget === 'file') {
            header('Content-Disposition: attachment; filename="' . $filename . '.pdf";');
        } else {
            header('Content-Disposition: inline; filename="' . $filename . '.pdf";');
        }

        //Bookcreator uses jQuery.fileDownload.js, which requires a cookie.
        header('Set-Cookie: fileDownload=true; path=/');

        //try to send file, and exit if done
        http_sendfile($cachefile);

        $fp = @fopen($cachefile, "rb");
        if($fp) {
            http_rangeRequest($fp, filesize($cachefile), 'application/pdf');
        } else {
            header("HTTP/1.0 500 Internal Server Error");
            print "Could not read file - bad permissions?";
        }
        exit();
    }

    /**
     * Load the various template files and prepare the HTML/CSS for insertion
     *
     * @return array
     */
    protected function load_template() {
        global $ID;
        global $conf;

        // this is what we'll return
        $output = array(
            'cover' => '',
            'html'  => '',
            'page'  => '',
            'first' => '',
            'cite'  => '',
        );

        // prepare header/footer elements
        $html = '';
        foreach(array('header', 'footer') as $section) {
            foreach(array('', '_odd', '_even', '_first') as $order) {
                $file = DOKU_PLUGIN . 'dw2pdf/tpl/' . $this->tpl . '/' . $section . $order . '.html';
                if(file_exists($file)) {
                    $html .= '<htmlpage' . $section . ' name="' . $section . $order . '">' . DOKU_LF;
                    $html .= file_get_contents($file) . DOKU_LF;
                    $html .= '</htmlpage' . $section . '>' . DOKU_LF;

                    // register the needed pseudo CSS
                    if($order == '_first') {
                        $output['first'] .= $section . ': html_' . $section . $order . ';' . DOKU_LF;
                    } elseif($order == '_even') {
                        $output['page'] .= 'even-' . $section . '-name: html_' . $section . $order . ';' . DOKU_LF;
                    } elseif($order == '_odd') {
                        $output['page'] .= 'odd-' . $section . '-name: html_' . $section . $order . ';' . DOKU_LF;
                    } else {
                        $output['page'] .= $section . ': html_' . $section . $order . ';' . DOKU_LF;
                    }
                }
            }
        }

        // prepare replacements
        $replace = array(
            '@PAGE@'    => '{PAGENO}',
            '@PAGES@'   => '{nbpg}', //see also $mpdf->pagenumSuffix = ' / '
            '@TITLE@'   => hsc($this->title),
            '@WIKI@'    => $conf['title'],
            '@WIKIURL@' => DOKU_URL,
            '@DATE@'    => dformat(time()),
            '@BASE@'    => DOKU_BASE,
            '@INC@'     => DOKU_INC,
            '@TPLBASE@' => DOKU_BASE . 'lib/plugins/dw2pdf/tpl/' . $this->tpl . '/',
            '@TPLINC@'  => DOKU_INC . 'lib/plugins/dw2pdf/tpl/' . $this->tpl . '/'
        );

        // set HTML element
        $html = str_replace(array_keys($replace), array_values($replace), $html);
        //TODO For bookcreator $ID (= bookmanager page) makes no sense
        $output['html'] = $this->page_depend_replacements($html, $ID);

        // cover page
        $coverfile = DOKU_PLUGIN . 'dw2pdf/tpl/' . $this->tpl . '/cover.html';
        if(file_exists($coverfile)) {
            $output['cover'] = file_get_contents($coverfile);
            $output['cover'] = str_replace(array_keys($replace), array_values($replace), $output['cover']);
            $output['cover'] = $this->page_depend_replacements($output['cover'], $ID);
            $output['cover'] .= '<pagebreak />';
        }

        // cover page
        $backfile = DOKU_PLUGIN . 'dw2pdf/tpl/' . $this->tpl . '/back.html';
        if(file_exists($backfile)) {
            $output['back'] = '<pagebreak />';
            $output['back'] .= file_get_contents($backfile);
            $output['back'] = str_replace(array_keys($replace), array_values($replace), $output['back']);
            $output['back'] = $this->page_depend_replacements($output['back'], $ID);
        }

        // citation box
        $citationfile = DOKU_PLUGIN . 'dw2pdf/tpl/' . $this->tpl . '/citation.html';
        if(file_exists($citationfile)) {
            $output['cite'] = file_get_contents($citationfile);
            $output['cite'] = str_replace(array_keys($replace), array_values($replace), $output['cite']);
        }

        return $output;
    }

    /**
     * @param string $raw code with placeholders
     * @param string $id  pageid
     * @return string
     */
    protected function page_depend_replacements($raw, $id) {
        global $REV, $DATE_AT;

        // generate qr code for this page using quickchart.io (Google infographics api was deprecated in March 14, 2019)
        $qr_code = '';
        if($this->getConf('qrcodesize')) {
            $url = urlencode(wl($id, '', '&', true));
            $qr_code = '<img src="https://quickchart.io/qr?size=' .
                $this->getConf('qrcodesize') . '&text=' . $url . '&margin=1&ecLevel=Q" />';
        }
        // prepare replacements
        $replace['@ID@']      = $id;
        $replace['@UPDATE@']  = dformat(filemtime(wikiFN($id, $REV)));

        $params = array();
        if($DATE_AT) {
            $params['at'] = $DATE_AT;
        } elseif($REV) {
            $params['rev'] = $REV;
        }
        $replace['@PAGEURL@'] = wl($id, $params, true, "&");
        $replace['@QRCODE@']  = $qr_code;

        $content = $raw;

        // let other plugins define their own replacements
        $evdata = ['id' => $id, 'replace' => &$replace, 'content' => &$content];
        $event = new Doku_Event('PLUGIN_DW2PDF_REPLACE', $evdata);
        if ($event->advise_before()) {
            $content = str_replace(array_keys($replace), array_values($replace), $raw);
        }

        // plugins may post-process HTML, e.g to clean up unused replacements
        $event->advise_after();

        // @DATE(<date>[, <format>])@
        $content = preg_replace_callback(
            '/@DATE\((.*?)(?:,\s*(.*?))?\)@/',
            array($this, 'replacedate'),
            $content
        );

        return $content;
    }


    /**
     * (callback) Replace date by request datestring
     * e.g. '%m(30-11-1975)' is replaced by '11'
     *
     * @param array $match with [0]=>whole match, [1]=> first subpattern, [2] => second subpattern
     * @return string
     */
    function replacedate($match) {
        global $conf;
        //no 2nd argument for default date format
        if($match[2] == null) {
            $match[2] = $conf['dformat'];
        }
        return strftime($match[2], strtotime($match[1]));
    }

    /**
     * Load all the style sheets and apply the needed replacements
     */
    protected function load_css() {
        global $conf;
        //reusue the CSS dispatcher functions without triggering the main function
        define('SIMPLE_TEST', 1);
        require_once(DOKU_INC . 'lib/exe/css.php');

        // prepare CSS files
        $files = array_merge(
            array(
                DOKU_INC . 'lib/styles/screen.css'
                    => DOKU_BASE . 'lib/styles/',
                DOKU_INC . 'lib/styles/print.css'
                    => DOKU_BASE . 'lib/styles/',
            ),
            $this->css_pluginPDFstyles(),
            array(
                DOKU_PLUGIN . 'dw2pdf/conf/style.css'
                    => DOKU_BASE . 'lib/plugins/dw2pdf/conf/',
                DOKU_PLUGIN . 'dw2pdf/tpl/' . $this->tpl . '/style.css'
                    => DOKU_BASE . 'lib/plugins/dw2pdf/tpl/' . $this->tpl . '/',
                DOKU_PLUGIN . 'dw2pdf/conf/style.local.css'
                    => DOKU_BASE . 'lib/plugins/dw2pdf/conf/',
            )
        );
        $css = '';
        foreach($files as $file => $location) {
            $display = str_replace(fullpath(DOKU_INC), '', fullpath($file));
            $css .= "\n/* XXXXXXXXX $display XXXXXXXXX */\n";
            $css .= css_loadfile($file, $location);
        }

        if(function_exists('css_parseless')) {
            // apply pattern replacements
            if (function_exists('css_styleini')) {
                // compatiblity layer for pre-Greebo releases of DokuWiki
                $styleini = css_styleini($conf['template']);
            } else {
                // Greebo functionality
                $styleUtils = new \dokuwiki\StyleUtils();
                $styleini = $styleUtils->cssStyleini($conf['template']);
            }
            $css = css_applystyle($css, $styleini['replacements']);

            // parse less
            $css = css_parseless($css);
        } else {
            // @deprecated 2013-12-19: fix backward compatibility
            $css = css_applystyle($css, DOKU_INC . 'lib/tpl/' . $conf['template'] . '/');
        }

        return $css;
    }

    /**
     * Returns a list of possible Plugin PDF Styles
     *
     * Checks for a pdf.css, falls back to print.css
     *
     * @author Andreas Gohr <andi@splitbrain.org>
     */
    protected function css_pluginPDFstyles() {
        $list = array();
        $plugins = plugin_list();

        $usestyle = explode(',', $this->getConf('usestyles'));
        foreach($plugins as $p) {
            if(in_array($p, $usestyle)) {
                $list[DOKU_PLUGIN . "$p/screen.css"] = DOKU_BASE . "lib/plugins/$p/";
                $list[DOKU_PLUGIN . "$p/screen.less"] = DOKU_BASE . "lib/plugins/$p/";

                $list[DOKU_PLUGIN . "$p/style.css"] = DOKU_BASE . "lib/plugins/$p/";
                $list[DOKU_PLUGIN . "$p/style.less"] = DOKU_BASE . "lib/plugins/$p/";
            }

            $list[DOKU_PLUGIN . "$p/all.css"] = DOKU_BASE . "lib/plugins/$p/";
            $list[DOKU_PLUGIN . "$p/all.less"] = DOKU_BASE . "lib/plugins/$p/";

            if(file_exists(DOKU_PLUGIN . "$p/pdf.css") || file_exists(DOKU_PLUGIN . "$p/pdf.less")) {
                $list[DOKU_PLUGIN . "$p/pdf.css"] = DOKU_BASE . "lib/plugins/$p/";
                $list[DOKU_PLUGIN . "$p/pdf.less"] = DOKU_BASE . "lib/plugins/$p/";
            } else {
                $list[DOKU_PLUGIN . "$p/print.css"] = DOKU_BASE . "lib/plugins/$p/";
                $list[DOKU_PLUGIN . "$p/print.less"] = DOKU_BASE . "lib/plugins/$p/";
            }
        }
        return $list;
    }

    /**
     * Returns array of pages which will be included in the exported pdf
     *
     * @return array
     */
    public function getExportedPages() {
        return $this->list;
    }

    /**
     * usort callback to sort by file lastmodified time
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    public function _datesort($a, $b) {
        if($b['rev'] < $a['rev']) return -1;
        if($b['rev'] > $a['rev']) return 1;
        return strcmp($b['id'], $a['id']);
    }

    /**
     * usort callback to sort by page id
     * @param array $a
     * @param array $b
     * @return int
     */
    public function _pagenamesort($a, $b) {
        // do not sort numbers before namespace separators
        $aID = str_replace(':', '/', $a['id']);
        $bID = str_replace(':', '/', $b['id']);
        if($aID <= $bID) return -1;
        if($aID > $bID) return 1;
        return 0;
    }

    /**
     * Return settings read from:
     *   1. url parameters
     *   2. plugin config
     *   3. global config
     *
     * @return array
     */
    protected function loadExportConfig() {
        global $INPUT;
        global $conf;

        $this->exportConfig = array();

        // decide on the paper setup from param or config
        $this->exportConfig['pagesize'] = $INPUT->str('pagesize', $this->getConf('pagesize'), true);
        $this->exportConfig['orientation'] = $INPUT->str('orientation', $this->getConf('orientation'), true);

        // decide on the font-size from param or config
        $this->exportConfig['font-size'] = $INPUT->str('font-size', $this->getConf('font-size'), true);

        $doublesided = $INPUT->bool('doublesided', (bool) $this->getConf('doublesided'));
        $this->exportConfig['doublesided'] = $doublesided ? '1' : '0';

        $hasToC = $INPUT->bool('toc', (bool) $this->getConf('toc'));
        $levels = array();
        if($hasToC) {
            $toclevels = $INPUT->str('toclevels', $this->getConf('toclevels'), true);
            list($top_input, $max_input) = explode('-', $toclevels, 2);
            list($top_conf, $max_conf) = explode('-', $this->getConf('toclevels'), 2);
            $bounds_input = array(
                'top' => array(
                    (int) $top_input,
                    (int) $top_conf
                ),
                'max' => array(
                    (int) $max_input,
                    (int) $max_conf
                )
            );
            $bounds = array(
                'top' => $conf['toptoclevel'],
                'max' => $conf['maxtoclevel']

            );
            foreach($bounds_input as $bound => $values) {
                foreach($values as $value) {
                    if($value > 0 && $value <= 5) {
                        //stop at valid value and store
                        $bounds[$bound] = $value;
                        break;
                    }
                }
            }

            if($bounds['max'] < $bounds['top']) {
                $bounds['max'] = $bounds['top'];
            }

            for($level = $bounds['top']; $level <= $bounds['max']; $level++) {
                $levels["H$level"] = $level - 1;
            }
        }
        $this->exportConfig['hasToC'] = $hasToC;
        $this->exportConfig['levels'] = $levels;

        $this->exportConfig['maxbookmarks'] = $INPUT->int('maxbookmarks', $this->getConf('maxbookmarks'), true);

        $tplconf = $this->getConf('template');
        $tpl = $INPUT->str('tpl', $tplconf, true);
        if(!is_dir(DOKU_PLUGIN . 'dw2pdf/tpl/' . $tpl)) {
            $tpl = $tplconf;
        }
        if(!$tpl){
            $tpl = 'default';
        }
        $this->exportConfig['template'] = $tpl;

        $this->exportConfig['isDebug'] = $conf['allowdebug'] && $INPUT->has('debughtml');
    }

    /**
     * Returns requested config
     *
     * @param string $name
     * @param mixed  $notset
     * @return mixed|bool
     */
    public function getExportConfig($name, $notset = false) {
        if ($this->exportConfig === null){
            $this->loadExportConfig();
        }

        if(isset($this->exportConfig[$name])){
            return $this->exportConfig[$name];
        }else{
            return $notset;
        }
    }

    /**
     * Add 'export pdf'-button to pagetools
     *
     * @param Doku_Event $event
     */
    public function addbutton(Doku_Event $event) {
        global $ID, $REV, $DATE_AT;

        if($this->getConf('showexportbutton') && $event->data['view'] == 'main') {
            $params = array('do' => 'export_pdf');
            if($DATE_AT) {
                $params['at'] = $DATE_AT;
            } elseif($REV) {
                $params['rev'] = $REV;
            }

            // insert button at position before last (up to top)
            $event->data['items'] = array_slice($event->data['items'], 0, -1, true) +
                array('export_pdf' =>
                          '<li>'
                          . '<a href="' . wl($ID, $params) . '"  class="action export_pdf" rel="nofollow" title="' . $this->getLang('export_pdf_button') . '">'
                          . '<span>' . $this->getLang('export_pdf_button') . '</span>'
                          . '</a>'
                          . '</li>'
                ) +
                array_slice($event->data['items'], -1, 1, true);
        }
    }

    /**
     * Add 'export pdf' button to page tools, new SVG based mechanism
     *
     * @param Doku_Event $event
     */
    public function addsvgbutton(Doku_Event $event) {
        global $INFO;
        if($event->data['view'] != 'page' || !$this->getConf('showexportbutton')) {
            return;
        }

        if(!$INFO['exists']) {
            return;
        }

        array_splice($event->data['items'], -1, 0, [new \dokuwiki\plugin\dw2pdf\MenuItem()]);
    }
}
