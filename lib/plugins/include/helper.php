<?php
/**
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Esther Brunner <wikidesign@gmail.com>
 * @author     Christopher Smith <chris@jalakai.co.uk>
 * @author     Gina Häußge, Michael Klier <dokuwiki@chimeric.de>
 * @author     Michael Hamann <michael@content-space.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) die();

if (!defined('DOKU_LF')) define('DOKU_LF', "\n");
if (!defined('DOKU_TAB')) define('DOKU_TAB', "\t");
if (!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN', DOKU_INC.'lib/plugins/');

/**
 * Helper functions for the include plugin and other plugins that want to include pages.
 */
class helper_plugin_include extends DokuWiki_Plugin { // DokuWiki_Helper_Plugin

    var $defaults  = array();
    var $sec_close = true;
    /** @var helper_plugin_tag $taghelper */
    var $taghelper = null;
    var $includes  = array(); // deprecated - compatibility code for the blog plugin

    /**
     * Constructor loads default config settings once
     */
    function __construct() {
        $this->defaults['noheader']  = $this->getConf('noheader');
        $this->defaults['firstsec']  = $this->getConf('firstseconly');
        $this->defaults['editbtn']   = $this->getConf('showeditbtn');
        $this->defaults['taglogos']  = $this->getConf('showtaglogos');
        $this->defaults['footer']    = $this->getConf('showfooter');
        $this->defaults['redirect']  = $this->getConf('doredirect');
        $this->defaults['date']      = $this->getConf('showdate');
        $this->defaults['mdate']     = $this->getConf('showmdate');
        $this->defaults['user']      = $this->getConf('showuser');
        $this->defaults['comments']  = $this->getConf('showcomments');
        $this->defaults['linkbacks'] = $this->getConf('showlinkbacks');
        $this->defaults['tags']      = $this->getConf('showtags');
        $this->defaults['link']      = $this->getConf('showlink');
        $this->defaults['permalink'] = $this->getConf('showpermalink');
        $this->defaults['indent']    = $this->getConf('doindent');
        $this->defaults['linkonly']  = $this->getConf('linkonly');
        $this->defaults['title']     = $this->getConf('title');
        $this->defaults['pageexists']  = $this->getConf('pageexists');
        $this->defaults['parlink']   = $this->getConf('parlink');
        $this->defaults['inline']    = false;
        $this->defaults['order']     = $this->getConf('order');
        $this->defaults['rsort']     = $this->getConf('rsort');
        $this->defaults['depth']     = $this->getConf('depth');
        $this->defaults['readmore']  = $this->getConf('readmore');
    }

    /**
     * Available methods for other plugins
     */
    function getMethods() {
        $result = array();
        $result[] = array(
                'name'   => 'get_flags',
                'desc'   => 'overrides standard values for showfooter and firstseconly settings',
                'params' => array('flags' => 'array'),
                );
        return $result;
    }

    /**
     * Overrides standard values for showfooter and firstseconly settings
     */
    function get_flags($setflags) {
        // load defaults
        $flags = $this->defaults;
        foreach ($setflags as $flag) {
            $value = '';
            if (strpos($flag, '=') !== -1) {
                list($flag, $value) = explode('=', $flag, 2);
            }
            switch ($flag) {
                case 'footer':
                    $flags['footer'] = 1;
                    break;
                case 'nofooter':
                    $flags['footer'] = 0;
                    break;
                case 'firstseconly':
                case 'firstsectiononly':
                    $flags['firstsec'] = 1;
                    break;
                case 'fullpage':
                    $flags['firstsec'] = 0;
                    break;
                case 'showheader':
                case 'header':
                    $flags['noheader'] = 0;
                    break;
                case 'noheader':
                    $flags['noheader'] = 1;
                    break;
                case 'editbtn':
                case 'editbutton':
                    $flags['editbtn'] = 1;
                    break;
                case 'noeditbtn':
                case 'noeditbutton':
                    $flags['editbtn'] = 0;
                    break;
                case 'permalink':
                    $flags['permalink'] = 1;
                    break;
                case 'nopermalink':
                    $flags['permalink'] = 0;
                    break;
                case 'redirect':
                    $flags['redirect'] = 1;
                    break;
                case 'noredirect':
                    $flags['redirect'] = 0;
                    break;
                case 'link':
                    $flags['link'] = 1;
                    break;
                case 'nolink':
                    $flags['link'] = 0;
                    break;
                case 'user':
                    $flags['user'] = 1;
                    break;
                case 'nouser':
                    $flags['user'] = 0;
                    break;
                case 'comments':
                    $flags['comments'] = 1;
                    break;
                case 'nocomments':
                    $flags['comments'] = 0;
                    break;
                case 'linkbacks':
                    $flags['linkbacks'] = 1;
                    break;
                case 'nolinkbacks':
                    $flags['linkbacks'] = 0;
                    break;
                case 'tags':
                    $flags['tags'] = 1;
                    break;
                case 'notags':
                    $flags['tags'] = 0;
                    break;
                case 'date':
                    $flags['date'] = 1;
                    break;
                case 'nodate':
                    $flags['date'] = 0;
                    break;
                case 'mdate':
                    $flags['mdate'] = 1;
                    break;
                case 'nomdate':
                    $flags['mdate'] = 0;
                    break;
                case 'indent':
                    $flags['indent'] = 1;
                    break;
                case 'noindent':
                    $flags['indent'] = 0;
                    break;
                case 'linkonly':
                    $flags['linkonly'] = 1;
                    break;
                case 'nolinkonly':
                case 'include_content':
                    $flags['linkonly'] = 0;
                    break;
                case 'inline':
                    $flags['inline'] = 1;
                    break;
                case 'title':
                    $flags['title'] = 1;
                    break;
                case 'notitle':
                    $flags['title'] = 0;
                    break;
                case 'pageexists':
                    $flags['pageexists'] = 1;
                    break;
                case 'nopageexists':
                    $flags['pageexists'] = 0;
                    break;
                case 'existlink':
                    $flags['pageexists'] = 1;
                    $flags['linkonly'] = 1;
                    break;
                case 'parlink':
                    $flags['parlink'] = 1;
                    break;
                case 'noparlink':
                    $flags['parlink'] = 0;
                    break;
                case 'order':
                    $flags['order'] = $value;
                    break;
                case 'sort':
                    $flags['rsort'] = 0;
                    break;
                case 'rsort':
                    $flags['rsort'] = 1;
                    break;
                case 'depth':
                    $flags['depth'] = max(intval($value), 0);
                    break;
                case 'beforeeach':
                    $flags['beforeeach'] = $value;
                    break;
                case 'aftereach':
                    $flags['aftereach'] = $value;
                    break;
                case 'readmore':
                    $flags['readmore'] = 1;
                    break;
                case 'noreadmore':
                    $flags['readmore'] = 0;
                    break;
            }
        }
        // the include_content URL parameter overrides flags
        if (isset($_REQUEST['include_content']))
            $flags['linkonly'] = 0;
        return $flags;
    }

    /**
     * Returns the converted instructions of a give page/section
     *
     * @author Michael Klier <chi@chimeric.de>
     * @author Michael Hamann <michael@content-space.de>
     */
    function _get_instructions($page, $sect, $mode, $lvl, $flags, $root_id = null, $included_pages = array()) {
        $key = ($sect) ? $page . '#' . $sect : $page;
        $this->includes[$key] = true; // legacy code for keeping compatibility with other plugins

        // keep compatibility with other plugins that don't know the $root_id parameter
        if (is_null($root_id)) {
            global $ID;
            $root_id = $ID;
        }

        if ($flags['linkonly']) {
            if (page_exists($page) || $flags['pageexists']  == 0) {
                $title = '';
                if ($flags['title'])
                    $title = p_get_first_heading($page);
                if($flags['parlink']) {
                    $ins = array(
                        array('p_open', array()),
                        array('internallink', array(':'.$key, $title)),
                        array('p_close', array()),
                    );
                } else {
                    $ins = array(array('internallink', array(':'.$key,$title)));
                }
            }else {
                $ins = array();
            }
        } else {
            if (page_exists($page)) {
                global $ID;
                $backupID = $ID;
                $ID = $page; // Change the global $ID as otherwise plugins like the discussion plugin will save data for the wrong page
                $ins = p_cached_instructions(wikiFN($page), false, $page);
                $ID = $backupID;
            } else {
                $ins = array();
            }

            $this->_convert_instructions($ins, $lvl, $page, $sect, $flags, $root_id, $included_pages);
        }
        return $ins;
    }

    /**
     * Converts instructions of the included page
     *
     * The funcion iterates over the given list of instructions and generates
     * an index of header and section indicies. It also removes document
     * start/end instructions, converts links, and removes unwanted
     * instructions like tags, comments, linkbacks.
     *
     * Later all header/section levels are convertet to match the current
     * inclusion level.
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _convert_instructions(&$ins, $lvl, $page, $sect, $flags, $root_id, $included_pages = array()) {
        global $conf;

        // filter instructions if needed
        if(!empty($sect)) {
            $this->_get_section($ins, $sect);   // section required
        }

        if($flags['firstsec']) {
            $this->_get_firstsec($ins, $page, $flags);  // only first section 
        }
        
        $ns  = getNS($page);
        $num = count($ins);

        $conv_idx = array(); // conversion index
        $lvl_max  = false;   // max level
        $first_header = -1;
        $no_header  = false;
        $sect_title = false;
        $endpos     = null; // end position of the raw wiki text

        $this->adapt_links($ins, $page, $included_pages);

        for($i=0; $i<$num; $i++) {
            switch($ins[$i][0]) {
                case 'document_start':
                case 'document_end':
                case 'section_edit':
                    unset($ins[$i]);
                    break;
                case 'header':
                    // get section title of first section
                    if($sect && !$sect_title) {
                        $sect_title = $ins[$i][1][0];
                    }
                    // check if we need to skip the first header
                    if((!$no_header) && $flags['noheader']) {
                        $no_header = true;
                    }

                    $conv_idx[] = $i;
                    // get index of first header
                    if($first_header == -1) $first_header = $i;
                    // get max level of this instructions set
                    if(!$lvl_max || ($ins[$i][1][1] < $lvl_max)) {
                        $lvl_max = $ins[$i][1][1];
                    }
                    break;
                case 'section_open':
                    if ($flags['inline'])
                        unset($ins[$i]);
                    else
                        $conv_idx[] = $i;
                    break;
                case 'section_close':
                    if ($flags['inline'])
                        unset($ins[$i]);
                    break;
                case 'nest':
                    $this->adapt_links($ins[$i][1][0], $page, $included_pages);
                    break;
                case 'plugin':
                    // FIXME skip other plugins?
                    switch($ins[$i][1][0]) {
                        case 'tag_tag':                 // skip tags
                        case 'discussion_comments':     // skip comments
                        case 'linkback':                // skip linkbacks
                        case 'data_entry':              // skip data plugin
                        case 'meta':                    // skip meta plugin
                        case 'indexmenu_tag':           // skip indexmenu sort tag
                        case 'include_sorttag':         // skip include plugin sort tag
                            unset($ins[$i]);
                            break;
                        // adapt indentation level of nested includes
                        case 'include_include':
                            if (!$flags['inline'] && $flags['indent'])
                                $ins[$i][1][1][4] += $lvl;
                            break;
                        /*
                         * if there is already a closelastsecedit instruction (was added by one of the section
                         * functions), store its position but delete it as it can't be determined yet if it is needed,
                         * i.e. if there is a header which generates a section edit (depends on the levels, level
                         * adjustments, $no_header, ...)
                         */
                        case 'include_closelastsecedit':
                            $endpos = $ins[$i][1][1][0];
                            unset($ins[$i]);
                            break;
                    }
                    break;
                default:
                    break;
            }
        }

        // calculate difference between header/section level and include level
        $diff = 0;
        if (!isset($lvl_max)) $lvl_max = 0; // if no level found in target, set to 0
        $diff = $lvl - $lvl_max + 1;
        if ($no_header) $diff -= 1;  // push up one level if "noheader"

        // convert headers and set footer/permalink
        $hdr_deleted      = false;
        $has_permalink    = false;
        $footer_lvl       = false;
        $contains_secedit = false;
        $section_close_at = false;
        foreach($conv_idx as $idx) {
            if($ins[$idx][0] == 'header') {
                if ($section_close_at === false && isset($ins[$idx+1]) && $ins[$idx+1][0] == 'section_open') {
                    // store the index of the first heading that is followed by a new section
                    // the wrap plugin creates sections without section_open so the section shouldn't be closed before them
                    $section_close_at = $idx;
                }

                if($no_header && !$hdr_deleted) {
                    unset ($ins[$idx]);
                    $hdr_deleted = true;
                    continue;
                }

                if($flags['indent']) {
                    $lvl_new = (($ins[$idx][1][1] + $diff) > 5) ? 5 : ($ins[$idx][1][1] + $diff);
                    $ins[$idx][1][1] = $lvl_new;
                }

                if($ins[$idx][1][1] <= $conf['maxseclevel'])
                    $contains_secedit = true;

                // set permalink
                if($flags['link'] && !$has_permalink && ($idx == $first_header)) {
                    $this->_permalink($ins[$idx], $page, $sect, $flags);
                    $has_permalink = true;
                }

                // set footer level
                if(!$footer_lvl && ($idx == $first_header) && !$no_header) {
                    if($flags['indent'] && isset($lvl_new)) {
                        $footer_lvl = $lvl_new;
                    } else {
                        $footer_lvl = $lvl_max;
                    }
                }
            } else {
                // it's a section
                if($flags['indent']) {
                    $lvl_new = (($ins[$idx][1][0] + $diff) > 5) ? 5 : ($ins[$idx][1][0] + $diff);
                    $ins[$idx][1][0] = $lvl_new;
                }

                // check if noheader is used and set the footer level to the first section
                if($no_header && !$footer_lvl) {
                    if($flags['indent'] && isset($lvl_new)) {
                        $footer_lvl = $lvl_new;
                    } else {
                        $footer_lvl = $lvl_max;
                    }
                } 
            }
        }

        // close last open section of the included page if there is any
        if ($contains_secedit) {
            array_push($ins, array('plugin', array('include_closelastsecedit', array($endpos))));
        }

        // add edit button
        if($flags['editbtn']) {
            $this->_editbtn($ins, $page, $sect, $sect_title, ($flags['redirect'] ? $root_id : false));
        }

        // add footer
        if($flags['footer']) {
            $ins[] = $this->_footer($page, $sect, $sect_title, $flags, $footer_lvl, $root_id);
        }

        // wrap content at the beginning of the include that is not in a section in a section
        if ($lvl > 0 && $section_close_at !== 0 && $flags['indent'] && !$flags['inline']) {
            if ($section_close_at === false) {
                $ins[] = array('section_close', array());
                array_unshift($ins, array('section_open', array($lvl)));
            } else {
                $section_close_idx = array_search($section_close_at, array_keys($ins));
                if ($section_close_idx > 0) {
                    $before_ins = array_slice($ins, 0, $section_close_idx);
                    $after_ins = array_slice($ins, $section_close_idx);
                    $ins = array_merge($before_ins, array(array('section_close', array())), $after_ins);
                    array_unshift($ins, array('section_open', array($lvl)));
                }
            }
        }

        // add instructions entry wrapper
        $include_secid = (isset($flags['include_secid']) ? $flags['include_secid'] : NULL);
        array_unshift($ins, array('plugin', array('include_wrap', array('open', $page, $flags['redirect'], $include_secid))));
        if (isset($flags['beforeeach']))
            array_unshift($ins, array('entity', array($flags['beforeeach'])));
        array_push($ins, array('plugin', array('include_wrap', array('close'))));
        if (isset($flags['aftereach']))
            array_push($ins, array('entity', array($flags['aftereach'])));

        // close previous section if any and re-open after inclusion
        if($lvl != 0 && $this->sec_close && !$flags['inline']) {
            array_unshift($ins, array('section_close', array()));
            $ins[] = array('section_open', array($lvl));
        }
    }

    /**
     * Appends instruction item for the include plugin footer
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _footer($page, $sect, $sect_title, $flags, $footer_lvl, $root_id) {
        $footer = array();
        $footer[0] = 'plugin';
        $footer[1] = array('include_footer', array($page, $sect, $sect_title, $flags, $root_id, $footer_lvl));
        return $footer;
    }

    /**
     * Appends instruction item for an edit button
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _editbtn(&$ins, $page, $sect, $sect_title, $root_id) {
        $title = ($sect) ? $sect_title : $page;
        $editbtn = array();
        $editbtn[0] = 'plugin';
        $editbtn[1] = array('include_editbtn', array($title));
        $ins[] = $editbtn;
    }

    /**
     * Convert instruction item for a permalink header
     * 
     * @author Michael Klier <chi@chimeric.de>
     */
    function _permalink(&$ins, $page, $sect, $flags) {
        $ins[0] = 'plugin';
        $ins[1] = array('include_header', array($ins[1][0], $ins[1][1], $ins[1][2], $page, $sect, $flags));
    }

    /**
     * Convert internal and local links depending on the included pages
     *
     * @param array  $ins            The instructions that shall be adapted
     * @param string $page           The included page
     * @param array  $included_pages The array of pages that are included
     */
    private function adapt_links(&$ins, $page, $included_pages = null) {
        $num = count($ins);
        $ns  = getNS($page);

        for($i=0; $i<$num; $i++) {
            // adjust links with image titles
            if (strpos($ins[$i][0], 'link') !== false && isset($ins[$i][1][1]) && is_array($ins[$i][1][1]) && $ins[$i][1][1]['type'] == 'internalmedia') {
                // resolve relative ids, but without cleaning in order to preserve the name
                $media_id = resolve_id($ns, $ins[$i][1][1]['src']);
                // make sure that after resolving the link again it will be the same link
                if ($media_id{0} != ':') $media_id = ':'.$media_id;
                $ins[$i][1][1]['src'] = $media_id;
            }
            switch($ins[$i][0]) {
                case 'internallink':
                case 'internalmedia':
                    // make sure parameters aren't touched
                    $link_params = '';
                    $link_id = $ins[$i][1][0];
                    $link_parts = explode('?', $link_id, 2);
                    if (count($link_parts) === 2) {
                        $link_id = $link_parts[0];
                        $link_params = $link_parts[1];
                    }
                    // resolve the id without cleaning it
                    $link_id = resolve_id($ns, $link_id, false);
                    // this id is internal (i.e. absolute) now, add ':' to make resolve_id work again
                    if ($link_id{0} != ':') $link_id = ':'.$link_id;
                    // restore parameters
                    $ins[$i][1][0] = ($link_params != '') ? $link_id.'?'.$link_params : $link_id;

                    if ($ins[$i][0] == 'internallink' && !empty($included_pages)) {
                        // change links to included pages into local links
                        // only adapt links without parameters
                        $link_id = $ins[$i][1][0];
                        $link_parts = explode('?', $link_id, 2);
                        if (count($link_parts) === 1) {
                            $exists = false;
                            resolve_pageid($ns, $link_id, $exists);

                            $link_parts = explode('#', $link_id, 2);
                            $hash = '';
                            if (count($link_parts) === 2) {
                                list($link_id, $hash) = $link_parts;
                            }
                            if (array_key_exists($link_id, $included_pages)) {
                                if ($hash) {
                                    // hopefully the hash is also unique in the including page (otherwise this might be the wrong link target)
                                    $ins[$i][0] = 'locallink';
                                    $ins[$i][1][0] = $hash;
                                } else {
                                    // the include section ids are different from normal section ids (so they won't conflict) but this
                                    // also means that the normal locallink function can't be used
                                    $ins[$i][0] = 'plugin';
                                    $ins[$i][1] = array('include_locallink', array($included_pages[$link_id]['hid'], $ins[$i][1][1], $ins[$i][1][0]));
                                }
                            }
                        }
                    }
                    break;
                case 'locallink':
                    /* Convert local links to internal links if the page hasn't been fully included */
                    if ($included_pages == null || !array_key_exists($page, $included_pages)) {
                        $ins[$i][0] = 'internallink';
                        $ins[$i][1][0] = ':'.$page.'#'.$ins[$i][1][0];
                    }
                    break;
            }
        }
    }

    /** 
     * Get a section including its subsections 
     *
     * @author Michael Klier <chi@chimeric.de>
     */ 
    function _get_section(&$ins, $sect) {
        $num = count($ins);
        $offset = false;
        $lvl    = false;
        $end    = false;
        $endpos = null; // end position in the input text, needed for section edit buttons

        $check = array(); // used for sectionID() in order to get the same ids as the xhtml renderer

        for($i=0; $i<$num; $i++) {
            if ($ins[$i][0] == 'header') { 

                // found the right header 
                if (sectionID($ins[$i][1][0], $check) == $sect) {
                    $offset = $i;
                    $lvl    = $ins[$i][1][1]; 
                } elseif ($offset && $lvl && ($ins[$i][1][1] <= $lvl)) {
                    $end = $i - $offset;
                    $endpos = $ins[$i][1][2]; // the position directly after the found section, needed for the section edit button
                    break;
                }
            }
        }
        $offset = $offset ? $offset : 0;
        $end = $end ? $end : ($num - 1);
        if(is_array($ins)) {
            $ins = array_slice($ins, $offset, $end);
            // store the end position in the include_closelastsecedit instruction so it can generate a matching button
            $ins[] = array('plugin', array('include_closelastsecedit', array($endpos)));
        }
    } 

    /**
     * Only display the first section of a page and a readmore link
     *
     * @author Michael Klier <chi@chimeric.de>
     */
    function _get_firstsec(&$ins, $page, $flags) {
        $num = count($ins);
        $first_sect = false;
        $endpos = null; // end position in the input text
        for($i=0; $i<$num; $i++) {
            if($ins[$i][0] == 'section_close') {
                $first_sect = $i;
            }
            if ($ins[$i][0] == 'header') {
                /*
                 * Store the position of the last header that is encountered. As section_close/open-instruction are
                 * always (unless some plugin modifies this) around a header instruction this means that the last
                 * position that is stored here is exactly the position of the section_close/open at which the content
                 * is truncated.
                 */
                $endpos = $ins[$i][1][2];
            }
            // only truncate the content and add the read more link when there is really
            // more than that first section
            if(($first_sect) && ($ins[$i][0] == 'section_open')) {
                $ins = array_slice($ins, 0, $first_sect);
                if ($flags['readmore']) {
                    $ins[] = array('plugin', array('include_readmore', array($page)));
                }
                $ins[] = array('section_close', array());
                // store the end position in the include_closelastsecedit instruction so it can generate a matching button
                $ins[] = array('plugin', array('include_closelastsecedit', array($endpos)));
                return;
            }
        }
    }

    /**
     * Gives a list of pages for a given include statement
     *
     * @author Michael Hamann <michael@content-space.de>
     */
    function _get_included_pages($mode, $page, $sect, $parent_id, $flags) {
        global $conf;
        $pages = array();
        switch($mode) {
        case 'namespace':
            $page  = cleanID($page);
            $ns    = utf8_encodeFN(str_replace(':', '/', $page));
            // depth is absolute depth, not relative depth, but 0 has a special meaning.
            $depth = $flags['depth'] ? $flags['depth'] + substr_count($page, ':') + ($page ? 1 : 0) : 0;
            search($pagearrays, $conf['datadir'], 'search_allpages', array('depth' => $depth, 'skipacl' => false), $ns);
            if (is_array($pagearrays)) {
                foreach ($pagearrays as $pagearray) {
                    if (!isHiddenPage($pagearray['id'])) // skip hidden pages
                        $pages[] = $pagearray['id'];
                }
            }
            break;
		case 'blinks':
            $page = $this->_apply_macro($page, $parent_id);
            resolve_pageid(getNS($parent_id), $page, $exists);
            @require_once(DOKU_INC.'inc/fulltext.php');
            $pagearrays = ft_backlinks($page,true);
            $this->taghelper =& plugin_load('helper', 'tag');
            $tags = $this->taghelper->getTopic(getNS($parent_id), null, $sect);
            foreach ($tags as $tag){
                $tagss[] = $tag['id'];
            }
            if(!empty($pagearrays)){
                foreach ($pagearrays as $pagearray) {
                    if(in_array($pagearray,$tagss)){
                        $pages[] = $pagearray;
                    }
                }
            }else{
                $pages[] = 'notes:dummy';
            }
            break;
        case 'tagtopic':
            if (!$this->taghelper)
                $this->taghelper =& plugin_load('helper', 'tag');
            if(!$this->taghelper) {
                msg('You have to install the tag plugin to use this functionality!', -1);
                return array();
            }
            $tag   = $page;
            $sect  = '';
            $pagearrays = $this->taghelper->getTopic('', null, $tag);
            foreach ($pagearrays as $pagearray) {
                $pages[] = $pagearray['id'];
            }
            break;
        default:
            $page = $this->_apply_macro($page, $parent_id);
            resolve_pageid(getNS($parent_id), $page, $exists); // resolve shortcuts and clean ID
            if (auth_quickaclcheck($page) >= AUTH_READ)
                $pages[] = $page;
        }

        if (count($pages) > 1) {
            if ($flags['order'] === 'id') {
                if ($flags['rsort']) {
                    usort($pages, array($this, '_r_strnatcasecmp'));
                } else {
                    natcasesort($pages);
                }
            } else {
                $ordered_pages = array();
                foreach ($pages as $page) {
                    $key = '';
                    switch ($flags['order']) {
                        case 'title':
                            $key = p_get_first_heading($page);
                            break;
                        case 'created':
                            $key = p_get_metadata($page, 'date created', METADATA_DONT_RENDER);
                            break;
                        case 'modified':
                            $key = p_get_metadata($page, 'date modified', METADATA_DONT_RENDER);
                            break;
                        case 'indexmenu':
                            $key = p_get_metadata($page, 'indexmenu_n', METADATA_RENDER_USING_SIMPLE_CACHE);
                            if ($key === null)
                                $key = '';
                            break;
                        case 'custom':
                            $key = p_get_metadata($page, 'include_n', METADATA_RENDER_USING_SIMPLE_CACHE);
                            if ($key === null)
                                $key = '';
                            break;
                    }
                    $key .= '_'.$page;
                    $ordered_pages[$key] = $page;
                }
                if ($flags['rsort']) {
                    uksort($ordered_pages, array($this, '_r_strnatcasecmp'));
                } else {
                    uksort($ordered_pages, 'strnatcasecmp');
                }
                $pages = $ordered_pages;
            }
        }

        $result = array();
        foreach ($pages as $page) {
            $exists = page_exists($page);
            $result[] = array('id' => $page, 'exists' => $exists, 'parent_id' => $parent_id);
        }
        return $result;
    }

    /**
     * String comparisons using a "natural order" algorithm in reverse order
     *
     * @link http://php.net/manual/en/function.strnatcmp.php
     * @param string $a First string
     * @param string $b Second string
     * @return int Similar to other string comparison functions, this one returns &lt; 0 if
     * str1 is greater than str2; &gt;
     * 0 if str1 is lesser than
     * str2, and 0 if they are equal.
     */
    function _r_strnatcasecmp($a, $b) {
        return strnatcasecmp($b, $a);
    }

    /**
     * This function generates the list of all included pages from a list of metadata
     * instructions.
     */
    function _get_included_pages_from_meta_instructions($instructions) {
        $pages = array();
        foreach ($instructions as $instruction) {
            $mode      = $instruction['mode'];
            $page      = $instruction['page'];
            $sect      = $instruction['sect'];
            $parent_id = $instruction['parent_id'];
            $flags     = $instruction['flags'];
            $pages = array_merge($pages, $this->_get_included_pages($mode, $page, $sect, $parent_id, $flags));
        }
        return $pages;
    }
    
    /**
     *  Get wiki language from "HTTP_ACCEPT_LANGUAGE"
     *  We allow the pattern e.g. "ja,en-US;q=0.7,en;q=0.3"
     */
    function _get_language_of_wiki($id, $parent_id) {
       global $conf;
       $result = $conf['lang'];
       if(strpos($id, '@BROWSER_LANG@') !== false){
           $brlangp = "/([a-zA-Z]{1,8}(-[a-zA-Z]{1,8})*|\*)(;q=(0(.[0-9]{0,3})?|1(.0{0,3})?))?/";
           if(preg_match_all(
               $brlangp, $_SERVER["HTTP_ACCEPT_LANGUAGE"],
               $matches, PREG_SET_ORDER
           )){
               $langs = array();
               foreach($matches as $match){
                   $langname = $match[1] == '*' ? $conf['lang'] : $match[1];
                   $qvalue = $match[4] == '' ? 1.0 : $match[4];
                   $langs[$langname] = $qvalue;
               }
               arsort($langs);
               foreach($langs as $lang => $langq){
                   $testpage = $this->_apply_macro(str_replace('@BROWSER_LANG@', $lang, $id), $parent_id);
                   resolve_pageid(getNS($parent_id), $testpage, $exists);
                   if($exists){
                       $result = $lang;
                       break;
                   }
               }
           }                                                                                   
       }                                                                                       
       return cleanID($result);                                                                
    }
    
    /**
     * Makes user or date dependent includes possible
     */
    function _apply_macro($id, $parent_id) {
        global $INFO;
        global $auth;
        
        // if we don't have an auth object, do nothing
        if (!$auth) return $id;

        $user     = $_SERVER['REMOTE_USER'];
        $group    = $INFO['userinfo']['grps'][0];

        // set group for unregistered users
        if (!isset($group)) {
            $group = 'ALL';
        }

        $time_stamp = time();
        if(preg_match('/@DATE(\w+)@/',$id,$matches)) {
            switch($matches[1]) {
            case 'PMONTH':
                $time_stamp = strtotime("-1 month");
                break;
            case 'NMONTH':
                $time_stamp = strtotime("+1 month");
                break;
            case 'NWEEK':
                $time_stamp = strtotime("+1 week");
                break;
            case 'PWEEK':
                $time_stamp = strtotime("-1 week");
                break;
            case 'TOMORROW':
                $time_stamp = strtotime("+1 day");
                break;
            case 'YESTERDAY':
                $time_stamp = strtotime("-1 day");
                break;
            case 'NYEAR':
                $time_stamp = strtotime("+1 year");
                break;
            case 'PYEAR':
                $time_stamp = strtotime("-1 year");
                break;
            }
            $id = preg_replace('/@DATE(\w+)@/','', $id);
        }

        $replace = array(
                '@USER@'  => cleanID($user),
                '@NAME@'  => cleanID($INFO['userinfo']['name']),
                '@GROUP@' => cleanID($group),
                '@BROWSER_LANG@'  => $this->_get_language_of_wiki($id, $parent_id),
                '@YEAR@'  => date('Y',$time_stamp),
                '@MONTH@' => date('m',$time_stamp),
                '@WEEK@' => date('W',$time_stamp),
                '@DAY@'   => date('d',$time_stamp),
                '@YEARPMONTH@' => date('Ym',strtotime("-1 month")),
                '@PMONTH@' => date('m',strtotime("-1 month")),
                '@NMONTH@' => date('m',strtotime("+1 month")),
                '@YEARNMONTH@' => date('Ym',strtotime("+1 month")),
                '@YEARPWEEK@' => date('YW',strtotime("-1 week")),
                '@PWEEK@' => date('W',strtotime("-1 week")),
                '@NWEEK@' => date('W',strtotime("+1 week")),
                '@YEARNWEEK@' => date('YW',strtotime("+1 week")),
                );
        return str_replace(array_keys($replace), array_values($replace), $id);
    }
}
// vim:ts=4:sw=4:et:
