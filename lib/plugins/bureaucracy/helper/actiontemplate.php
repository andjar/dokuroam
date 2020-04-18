<?php
/**
 * Simple template replacement action for the bureaucracy plugin
 *
 * @author Michael Klier <chi@chimeric.de>
 */

class helper_plugin_bureaucracy_actiontemplate extends helper_plugin_bureaucracy_action {

    var $targetpages;
    var $pagename;

    /**
     * Performs template action
     *
     * @param helper_plugin_bureaucracy_field[] $fields  array with form fields
     * @param string $thanks  thanks message
     * @param array  $argv    array with entries: template, pagename, separator
     * @return array|mixed
     *
     * @throws Exception
     */
    public function run($fields, $thanks, $argv) {
        global $conf;

        list($tpl, $this->pagename, $sep) = $argv;
        if(is_null($sep)) $sep = $conf['sepchar'];

        $this->patterns = array();
        $this->values   = array();
        $this->targetpages = array();

        $this->prepareNamespacetemplateReplacements();
        $this->prepareDateTimereplacements();
        $this->prepareLanguagePlaceholder();
        $this->prepareNoincludeReplacement();
        $this->prepareFieldReplacements($fields);

        $this->buildTargetPagename($fields, $sep);

        //target&template(s) from addpage fields
        $this->getAdditionalTargetpages($fields);
        //target&template(s) from action field
        $tpl = $this->getActionTargetpages($tpl);

        if(empty($this->targetpages)) {
            throw new Exception(sprintf($this->getLang('e_template'), $tpl));
        }

        $this->checkTargetPageNames();

        $this->processUploads($fields);
        $this->replaceAndSavePages($fields);

        $ret = $this->buildThankYouPage($thanks);

        return $ret;
    }

    /**
     * Prepare and resolve target page
     *
     * @param helper_plugin_bureaucracy_field[]  $fields  List of field objects
     * @param string                             $sep     Separator between fields for page id
     * @throws Exception missing pagename
     */
    protected function buildTargetPagename($fields, $sep) {
        global $ID;

        foreach ($fields as $field) {
            $pname = $field->getParam('pagename');
            if (!is_null($pname)) {
                if (is_array($pname)) $pname = implode($sep, $pname);
                $this->pagename .= $sep . $pname;
            }
        }

        $this->pagename = $this->replace($this->pagename);

        $myns = getNS($ID);
        resolve_pageid($myns, $this->pagename, $ignored); // resolve relatives

        if ($this->pagename === '') {
            throw new Exception($this->getLang('e_pagename'));
        }
    }

    /**
     * Handle templates from addpage field
     *
     * @param helper_plugin_bureaucracy_field[]  $fields  List of field objects
     * @return array
     */
    function getAdditionalTargetpages($fields) {
        global $ID;
        $ns = getNS($ID);

        foreach ($fields as $field) {
            if (!is_null($field->getParam('page_tpl')) && !is_null($field->getParam('page_tgt')) ) {
                //template
                $templatepage = $this->replace($field->getParam('page_tpl'));
                resolve_pageid(getNS($ID), $templatepage, $ignored);

                //target
                $relativetargetpage = $field->getParam('page_tgt');
                $relativetargetpage = $this->replace($relativetargetpage);
                resolve_pageid($ns, $relativeTargetPageid, $ignored);
                $targetpage = "$this->pagename:$relativetargetpage";

                $auth = $this->aclcheck($templatepage); // runas
                if ($auth >= AUTH_READ ) {
                    $this->addParsedTargetpage($targetpage, $templatepage);
                }
            }
        }
    }

    /**
     * Returns raw pagetemplate contents for the ID's namespace
     *
     * @param string $id the id of the page to be created
     * @return string raw pagetemplate content
     */
    protected function rawPageTemplate($id) {
        global $conf;

        $path = dirname(wikiFN($id));
        if(file_exists($path.'/_template.txt')) {
            $tplfile = $path.'/_template.txt';
        } else {
            // search upper namespaces for templates
            $len = strlen(rtrim($conf['datadir'], '/'));
            while(strlen($path) >= $len) {
                if(file_exists($path.'/__template.txt')) {
                    $tplfile = $path.'/__template.txt';
                    break;
                }
                $path = substr($path, 0, strrpos($path, '/'));
            }
        }

        $tpl = io_readFile($tplfile);
        return $tpl;
    }

    /**
     * Load template(s) for targetpage as given via action field
     *
     * @param string $tpl    template name as given in form
     * @return string parsed templatename
     */
    protected function getActionTargetpages($tpl) {
        global $USERINFO;
        global $conf;
        global $ID;
        $runas = $this->getConf('runas');

        if ($tpl == '_') {
            // use namespace template
            if (!isset($this->targetpages[$this->pagename])) {
                $raw = $this->rawPageTemplate($this->pagename);
                $this->noreplace_save($raw);
                $this->targetpages[$this->pagename] = pageTemplate(array($this->pagename));
            }
        } elseif ($tpl !== '!') {
            $tpl = $this->replace($tpl);

            // resolve templates, but keep references to whole namespaces intact (ending in a colon)
            if(substr($tpl, -1) == ':') {
                $tpl = $tpl.'xxx'; // append a fake page name
                resolve_pageid(getNS($ID), $tpl, $ignored);
                $tpl = substr($tpl, 0, -3); // cut off fake page name again
            } else {
                resolve_pageid(getNS($ID), $tpl, $ignored);
            }

            $backup = array();
            if ($runas) {
                // Hack user credentials.
                $backup = array($_SERVER['REMOTE_USER'], $USERINFO['grps']);
                $_SERVER['REMOTE_USER'] = $runas;
                $USERINFO['grps'] = array();
            }

            $template_pages = array();
            //search checks acl (as runas)
            $opts = array(
                'depth' => 0,
                'listfiles' => true,
                'showhidden' => true
            );
            search($template_pages, $conf['datadir'], 'search_universal', $opts, str_replace(':', '/', getNS($tpl)));

            foreach ($template_pages as $template_page) {
                $templatepageid = cleanID($template_page['id']);
                // try to replace $tpl path with $this->pagename path in the founded $templatepageid
                // - a single-page template will only match on itself and will be replaced,
                //   other newtargets are pages in same namespace, so aren't changed
                // - a namespace as template will match at the namespaces-part of the path of pages in this namespace
                //   so these newtargets are changed
                // if there exist a single-page and a namespace with name $tpl, both are selected
                $newTargetpageid = preg_replace('/^' . preg_quote_cb(cleanID($tpl)) . '($|:)/', $this->pagename . '$1', $templatepageid);

                if ($newTargetpageid === $templatepageid) {
                    // only a single-page template or page in the namespace template
                    // which matches the $tpl path are changed
                    continue;
                }

                if (!isset($this->targetpages[$newTargetpageid])) {
                    $this->addParsedTargetpage($newTargetpageid, $templatepageid);
                }
            }

            if ($runas) {
                /* Restore user credentials. */
                list($_SERVER['REMOTE_USER'], $USERINFO['grps']) = $backup;
            }
        }
        return $tpl;
    }

    /**
     * Checks for existance and access of target pages
     *
     * @return mixed
     * @throws Exception
     */
    protected function checkTargetPageNames() {
        foreach (array_keys($this->targetpages) as $pname) {
            // prevent overriding already existing pages
            if (page_exists($pname)) {
                throw new Exception(sprintf($this->getLang('e_pageexists'), html_wikilink($pname)));
            }

            $auth = $this->aclcheck($pname);
            if ($auth < AUTH_CREATE) {
                throw new Exception($this->getLang('e_denied'));
            }
        }
    }

    /**
     * Perform replacements on the collected templates, and save the pages.
     *
     * Note: wrt runas, for changelog are used:
     *  - $INFO['userinfo']['name']
     *  - $INPUT->server->str('REMOTE_USER')
     */
    protected function replaceAndSavePages($fields) {
        global $ID;
        foreach ($this->targetpages as $pageName => $template) {
            // set NSBASE var to make certain dataplugin constructs easier
            $this->patterns['__nsbase__'] = '/@NSBASE@/';
            $this->values['__nsbase__'] = noNS(getNS($pageName));

            $evdata = array(
                'patterns' => &$this->patterns,
                'values' => &$this->values,
                'id' => $pageName,
                'template' => $template,
                'form' => $ID,
                'fields' => $fields
            );

            $event = new Doku_Event('PLUGIN_BUREAUCRACY_TEMPLATE_SAVE', $evdata);
            if($event->advise_before()) {
                // save page
                saveWikiText(
                    $evdata['id'],
                    cleanText($this->replace($evdata['template'], false)),
                    sprintf($this->getLang('summary'), $ID)
                );
            }
            $event->advise_after();
        }
    }

    /**
     * (Callback) Sorts first by namespace depth, next by page ids
     *
     * @param string $a
     * @param string $b
     * @return int positive if $b is in deeper namespace than $a, negative higher.
     *             further sorted by pageids
     *
     *  return an integer less than, equal to, or
     * greater than zero if the first argument is considered to be
     * respectively less than, equal to, or greater than the second.
     */
    public function _sorttargetpages($a, $b) {
        $ns_diff = substr_count($a, ':') - substr_count($b, ':');
        return ($ns_diff === 0) ? strcmp($a, $b) : ($ns_diff > 0 ? -1 : 1);
    }

    /**
     * (Callback) Build content of item
     *
     * @param array $item
     * @return string
     */
    public function html_list_index($item){
        $ret = '';
        if($item['type']=='f'){
            $ret .= html_wikilink(':'.$item['id']);
        } else {
            $ret .= '<strong>' . trim(substr($item['id'], strrpos($item['id'], ':', -2)), ':') . '</strong>';
        }
        return $ret;
    }

    /**
     * Build thanks message, trigger indexing and rendering of new pages.
     *
     * @param string $thanks
     * @return string html of thanks message or when redirect the first page id of created pages
     */
    protected function buildThankYouPage($thanks) {
        global $ID;
        $backupID = $ID;

        $html = "<p>$thanks</p>";

        // Build result tree
        $pages = array_keys($this->targetpages);
        usort($pages, array($this, '_sorttargetpages'));

        $data = array();
        $last_folder = array();
        foreach ($pages as $ID) {
            $lvl = substr_count($ID, ':');
            for ($n = 0; $n < $lvl; ++$n) {
                if (!isset($last_folder[$n]) || strpos($ID, $last_folder[$n]['id']) !== 0) {
                    $last_folder[$n] = array(
                        'id' => substr($ID, 0, strpos($ID, ':', ($n > 0 ? strlen($last_folder[$n - 1]['id']) : 0) + 1) + 1),
                        'level' => $n + 1,
                        'open' => 1
                    );
                    $data[] = $last_folder[$n];
                }
            }
            $data[] = array('id' => $ID, 'level' => 1 + substr_count($ID, ':'), 'type' => 'f');
        }
        $html .= html_buildlist($data, 'idx', array($this, 'html_list_index'), 'html_li_index');

        // Add indexer bugs for every just-created page
        $html .= '<div class="no">';
        ob_start();
        foreach ($pages as $ID) {
            // indexerWebBug uses ID and INFO[exists], but the bureaucracy form
            // page always exists, as does the just-saved page, so INFO[exists]
            // is correct in any case
            tpl_indexerWebBug();

            // the iframe will trigger real rendering of the pages to make sure
            // any used plugins are initialized (eg. the do plugin)
            echo '<iframe src="' . wl($ID, array('do' => 'export_html')) . '" width="1" height="1" style="visibility:hidden"></iframe>';
        }
        $html .= ob_get_contents();
        ob_end_clean();
        $html .= '</div>';

        $ID = $backupID;
        return $html;
    }

    /**
     * move the uploaded files to <pagename>:FILENAME
     *
     *
     * @param helper_plugin_bureaucracy_field[] $fields
     * @throws Exception
     */
    protected function processUploads($fields) {
        foreach($fields as $field) {

            if($field->getFieldType() !== 'file') continue;

            $label = $field->getParam('label');
            $file  = $field->getParam('file');
            $ns    = $field->getParam('namespace');

            //skip empty files
            if(!$file['size']) {
                $this->values[$label] = '';
                continue;
            }

            $id = $ns.':'.$file['name'];
            resolve_mediaid($this->pagename, $id, $ignored); // resolve relatives

            $auth = $this->aclcheck($id); // runas
            $move = 'copy_uploaded_file';
            //prevent from is_uploaded_file() check
            if(defined('DOKU_UNITTEST')) {
                $move = 'copy';
            }
            $res = media_save(
                array('name' => $file['tmp_name']),
                $id,
                false,
                $auth,
                $move);

            if(is_array($res)) throw new Exception($res[0]);

            $this->values[$label] = $res;

        }
    }

    /**
     * Load page data and do default pattern replacements like namespace templates do
     * and add it to list of targetpages
     *
     * Note: for runas the values of the real user are used for the placeholders
     *       @NAME@ => $USERINFO['name']
     *       @MAIL@ => $USERINFO['mail']
     *       and the replaced value:
     *       @USER@ => $INPUT->server->str('REMOTE_USER')
     *
     * @param string $targetpageid   pageid of destination
     * @param string $templatepageid pageid of template for this targetpage
     */
    protected function addParsedTargetpage($targetpageid, $templatepageid) {
        $tpl = rawWiki($templatepageid);
        $this->noreplace_save($tpl);

        $data = array(
            'id' => $targetpageid,
            'tpl' => $tpl,
            'doreplace' => true,
        );
        parsePageTemplate($data);

        //collect and apply some other replacements
        $patterns = array();
        $values = array();
        $keys = array('__lang__', '__trans__', '__year__', '__month__', '__day__', '__time__');
        foreach($keys as $key) {
            $patterns[$key] = $this->patterns[$key];
            $values[$key] = $this->values[$key];
        }

        $this->targetpages[$targetpageid] = preg_replace($patterns, $values, $data['tpl']);
    }

}
// vim:ts=4:sw=4:et:enc=utf-8:
