<?php
/**
 * Include Plugin:  Display a wiki page within another wiki page
 *
 * Action plugin component, for cache validity determination
 * 
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Christopher Smith <chris@jalakai.co.uk>  
 * @author     Michael Klier <chi@chimeric.de>
 */
if(!defined('DOKU_INC')) die();  // no Dokuwiki, no go
 
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');
require_once(DOKU_PLUGIN.'action.php');
 
/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class action_plugin_include extends DokuWiki_Action_Plugin {
 
    /* @var helper_plugin_include $helper */
    var $helper = null;

    function __construct() {
        $this->helper = plugin_load('helper', 'include');
    }
 
    /**
     * plugin should use this method to register its handlers with the dokuwiki's event controller
     */
    function register(Doku_Event_Handler $controller) {
        /* @var Doku_event_handler $controller */
        $controller->register_hook('INDEXER_PAGE_ADD', 'BEFORE', $this, 'handle_indexer');
        $controller->register_hook('INDEXER_VERSION_GET', 'BEFORE', $this, 'handle_indexer_version');
      $controller->register_hook('PARSER_CACHE_USE','BEFORE', $this, '_cache_prepare');
      $controller->register_hook('HTML_EDITFORM_OUTPUT', 'BEFORE', $this, 'handle_form');
      $controller->register_hook('HTML_CONFLICTFORM_OUTPUT', 'BEFORE', $this, 'handle_form');
      $controller->register_hook('HTML_DRAFTFORM_OUTPUT', 'BEFORE', $this, 'handle_form');
      $controller->register_hook('ACTION_SHOW_REDIRECT', 'BEFORE', $this, 'handle_redirect');
      $controller->register_hook('PARSER_HANDLER_DONE', 'BEFORE', $this, 'handle_parser');
      $controller->register_hook('PARSER_METADATA_RENDER', 'AFTER', $this, 'handle_metadata');
      $controller->register_hook('HTML_SECEDIT_BUTTON', 'BEFORE', $this, 'handle_secedit_button');
        $controller->register_hook('PLUGIN_MOVE_HANDLERS_REGISTER', 'BEFORE', $this, 'handle_move_register');
    }

    /**
     * Add a version string to the index so it is rebuilt
     * whenever the handler is updated or the safeindex setting is changed
     */
    public function handle_indexer_version($event, $param) {
        $event->data['plugin_include'] = '0.1.safeindex='.$this->getConf('safeindex');
    }

    /**
     * Handles the INDEXER_PAGE_ADD event, prevents indexing of metadata from included pages that aren't public if enabled
     *
     * @param Doku_Event $event  the event object
     * @param array      $params optional parameters (unused)
     */
    public function handle_indexer(Doku_Event $event, $params) {
        global $USERINFO;

        // check if the feature is enabled at all
        if (!$this->getConf('safeindex')) return;

        // is there a user logged in at all? If not everything is fine already
        if (is_null($USERINFO) && !isset($_SERVER['REMOTE_USER'])) return;

        // get the include metadata in order to see which pages were included
        $inclmeta = p_get_metadata($event->data['page'], 'plugin_include', METADATA_RENDER_UNLIMITED);
        $all_public = true; // are all included pages public?
        // check if the current metadata indicates that non-public pages were included
        if ($inclmeta !== null && isset($inclmeta['pages'])) {
            foreach ($inclmeta['pages'] as $page) {
                if (auth_aclcheck($page['id'], '', array()) < AUTH_READ) { // is $page public?
                    $all_public = false;
                    break;
                }
            }
        }

        if (!$all_public) { // there were non-public pages included - action required!
            // backup the user information
            $userinfo_backup = $USERINFO;
            $remote_user = $_SERVER['REMOTE_USER'];
            // unset user information - temporary logoff!
            $USERINFO = null;
            unset($_SERVER['REMOTE_USER']);

            // metadata is only rendered once for a page in one request - thus we need to render manually.
            $meta = p_read_metadata($event->data['page']); // load the original metdata
            $meta = p_render_metadata($event->data['page'], $meta); // render the metadata
            p_save_metadata($event->data['page'], $meta); // save the metadata so other event handlers get the public metadata, too

            $meta = $meta['current']; // we are only interested in current metadata.

            // check if the tag plugin handler has already been called before the include plugin
            $tag_called = isset($event->data['metadata']['subject']);

            // Reset the metadata in the renderer. This removes data from all other event handlers, but we need to be on the safe side here.
            $event->data['metadata'] = array('title' => $meta['title']);

            // restore the relation references metadata
            if (isset($meta['relation']['references'])) {
                $event->data['metadata']['relation_references'] = array_keys($meta['relation']['references']);
            } else {
                $event->data['metadata']['relation_references'] = array();
            }

            // restore the tag metadata if the tag plugin handler has been called before the include plugin handler.
            if ($tag_called) {
                $tag_helper = $this->loadHelper('tag', false);
                if ($tag_helper) {
                    if (isset($meta['subject']))  {
                        $event->data['metadata']['subject'] = $tag_helper->_cleanTagList($meta['subject']);
                    } else {
                        $event->data['metadata']['subject'] = array();
                    }
                }
            }

            // restore user information
            $USERINFO = $userinfo_backup;
            $_SERVER['REMOTE_USER'] = $remote_user;
        }
    }

    /**
     * Used for debugging purposes only
     */
    function handle_metadata(&$event, $param) {
        global $conf;
        if($conf['allowdebug'] && $this->getConf('debugoutput')) {
            dbglog('---- PLUGIN INCLUDE META DATA START ----');
            dbglog($event->data);
            dbglog('---- PLUGIN INCLUDE META DATA END ----');
        }
    }

    /**
     * Supplies the current section level to the include syntax plugin
     *
     * @author Michael Klier <chi@chimeric.de>
     * @author Michael Hamann <michael@content-space.de>
     */
    function handle_parser(Doku_Event $event, $param) {
        global $ID;

        $level = 0;
        $ins =& $event->data->calls;
        $num = count($ins);
        for($i=0; $i<$num; $i++) {
            switch($ins[$i][0]) {
            case 'plugin':
                switch($ins[$i][1][0]) {
                case 'include_include':
                    $ins[$i][1][1][4] = $level;
                    break;
                    /* FIXME: this doesn't work anymore that way with the new structure
                    // some plugins already close open sections
                    // so we need to make sure we don't close them twice
                case 'box':
                    $this->helper->sec_close = false;
                    break;
                     */
                }
                break;
            case 'section_open':
                $level = $ins[$i][1][0];
                break;
            }
        }
    }

    /**
     * Add a hidden input to the form to preserve the redirect_id
     */
    function handle_form(Doku_Event &$event, $param) {
      if (array_key_exists('redirect_id', $_REQUEST)) {
        $event->data->addHidden('redirect_id', cleanID($_REQUEST['redirect_id']));
      }
    }

    /**
     * Modify the data for the redirect when there is a redirect_id set
     */
    function handle_redirect(Doku_Event &$event, $param) {
      if (array_key_exists('redirect_id', $_REQUEST)) {
        // Render metadata when this is an older DokuWiki version where
        // metadata is not automatically re-rendered as the page has probably
        // been changed but is not directly displayed
        $versionData = getVersionData();
        if ($versionData['date'] < '2010-11-23') {
            p_set_metadata($event->data['id'], array(), true);
        }
        $event->data['id'] = cleanID($_REQUEST['redirect_id']);
        $event->data['title'] = '';
      }
    }

    /**
     * prepare the cache object for default _useCache action
     */
    function _cache_prepare(Doku_Event &$event, $param) {
        global $conf;

        /* @var cache_renderer $cache */
        $cache =& $event->data;

        if(!isset($cache->page)) return;
        if(!isset($cache->mode) || $cache->mode == 'i') return;

        $depends = p_get_metadata($cache->page, 'plugin_include');

        if($conf['allowdebug'] && $this->getConf('debugoutput')) {
            dbglog('---- PLUGIN INCLUDE CACHE DEPENDS START ----');
            dbglog($depends);
            dbglog('---- PLUGIN INCLUDE CACHE DEPENDS END ----');
        }

        if (!is_array($depends)) return; // nothing to do for us

        if (!is_array($depends['pages']) ||
            !is_array($depends['instructions']) ||
            $depends['pages'] != $this->helper->_get_included_pages_from_meta_instructions($depends['instructions']) ||
            // the include_content url parameter may change the behavior for included pages
            $depends['include_content'] != isset($_REQUEST['include_content'])) {

            $cache->depends['purge'] = true; // included pages changed or old metadata - request purge.
            if($conf['allowdebug'] && $this->getConf('debugoutput')) {
                dbglog('---- PLUGIN INCLUDE: REQUESTING CACHE PURGE ----');
                dbglog('---- PLUGIN INCLUDE CACHE PAGES FROM META START ----');
                dbglog($depends['pages']);
                dbglog('---- PLUGIN INCLUDE CACHE PAGES FROM META END ----');
                dbglog('---- PLUGIN INCLUDE CACHE PAGES FROM META_INSTRUCTIONS START ----');
                dbglog($this->helper->_get_included_pages_from_meta_instructions($depends['instructions']));
                dbglog('---- PLUGIN INCLUDE CACHE PAGES FROM META_INSTRUCTIONS END ----');

            }
        } else {
            // add plugin.info.txt to depends for nicer upgrades
            $cache->depends['files'][] = dirname(__FILE__) . '/plugin.info.txt';
            foreach ($depends['pages'] as $page) {
                if (!$page['exists']) continue;
                $file = wikiFN($page['id']);
                if (!in_array($file, $cache->depends['files'])) {
                    $cache->depends['files'][] = $file;
                }
            }
        }
    }

    /**
     * Handle special section edit buttons for the include plugin to get the current page
     * and replace normal section edit buttons when the current page is different from the
     * global $ID.
     */
    function handle_secedit_button(Doku_Event &$event, $params) {
        // stack of included pages in the form ('id' => page, 'rev' => modification time, 'writable' => bool)
        static $page_stack = array();

        global $ID, $lang;

        $data = $event->data;

        if ($data['target'] == 'plugin_include_start' || $data['target'] == 'plugin_include_start_noredirect') {
            // handle the "section edits" added by the include plugin
            $fn = wikiFN($data['name']);
            $perm = auth_quickaclcheck($data['name']);
            array_unshift($page_stack, array(
                'id' => $data['name'],
                'rev' => @filemtime($fn),
                'writable' => (page_exists($data['name']) ? (is_writable($fn) && $perm >= AUTH_EDIT) : $perm >= AUTH_CREATE),
                'redirect' => ($data['target'] == 'plugin_include_start'),
            ));
        } elseif ($data['target'] == 'plugin_include_end') {
            array_shift($page_stack);
        } elseif ($data['target'] == 'plugin_include_editbtn') {
            if ($page_stack[0]['writable']) {
                $params = array('do' => 'edit',
                    'id' => $page_stack[0]['id']);
                if ($page_stack[0]['redirect'])
                    $params['redirect_id'] = $ID;
                $event->result = '<div class="secedit">' . DOKU_LF .
                    html_btn('incledit', $page_stack[0]['id'], '',
                        $params, 'post',
                        $data['name'],
                        $lang['btn_secedit'].' ('.$page_stack[0]['id'].')') .
                    '</div>' . DOKU_LF;
            }
        } elseif (!empty($page_stack)) {

            // Special handling for the edittable plugin
            if ($data['target'] == 'table' && !plugin_isdisabled('edittable')) {
                /* @var action_plugin_edittable_editor $edittable */
                $edittable =& plugin_load('action', 'edittable_editor');
                if (is_null($edittable))
                    $edittable =& plugin_load('action', 'edittable');
                $data['name'] = $edittable->getLang('secedit_name');
            }

            if ($page_stack[0]['writable'] && isset($data['name']) && $data['name'] !== '') {
                $name = $data['name'];
                unset($data['name']);

                $secid = $data['secid'];
                unset($data['secid']);

                if ($page_stack[0]['redirect'])
                    $data['redirect_id'] = $ID;

                $event->result = "<div class='secedit editbutton_" . $data['target'] .
                    " editbutton_" . $secid . "'>" .
                    html_btn('secedit', $page_stack[0]['id'], '',
                        array_merge(array('do'  => 'edit',
                        'rev' => $page_stack[0]['rev'],
                        'summary' => '['.$name.'] '), $data),
                        'post', $name) . '</div>';
            } else {
                $event->result = '';
            }
        } else {
            return; // return so the event won't be stopped
        }

        $event->preventDefault();
        $event->stopPropagation();
    }

    public function handle_move_register(Doku_Event $event, $params) {
        $event->data['handlers']['include_include'] = array($this, 'rewrite_include');
    }

    public function rewrite_include($match, $pos, $state, $plugin, helper_plugin_move_handler $handler) {
        $syntax = substr($match, 2, -2); // strip markup
        $replacers = explode('|', $syntax);
        $syntax = array_shift($replacers);
        list($syntax, $flags) = explode('&', $syntax, 2);

        // break the pattern up into its parts
        list($mode, $page, $sect) = preg_split('/>|#/u', $syntax, 3);

        if (method_exists($handler, 'adaptRelativeId')) { // move plugin before version 2015-05-16
            $newpage = $handler->adaptRelativeId($page);
        } else {
            $newpage = $handler->resolveMoves($page, 'page');
            $newpage = $handler->relativeLink($page, $newpage, 'page');
        }

        if ($newpage == $page) {
            return $match;
        } else {
            $result = '{{'.$mode.'>'.$newpage;
            if ($sect) $result .= '#'.$sect;
            if ($flags) $result .= '&'.$flags;
            if ($replacers) $result .= '|'.$replacers;
            $result .= '}}';
            return $result;
        }
    }
}
// vim:ts=4:sw=4:et:
