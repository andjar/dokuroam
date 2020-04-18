<?php
/**
 *  nosidebar action plugin
 *
 *  @license      GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *  @author       Matthias Schulte <dokuwiki@lupo49.de>
 *  @version      2013-07-14
 */

if(!defined('DOKU_INC')) die();
if(!defined('DOKU_PLUGIN')) define('DOKU_PLUGIN',DOKU_INC.'lib/plugins/');

require_once(DOKU_PLUGIN.'action.php');

class action_plugin_nosidebar extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'nosidebar', array());
    }

    function nosidebar(&$event, $param) {
        global $INFO;
        global $conf;
        if(empty($INFO['meta']['nosidebar'])) return;

        if($INFO['meta']['nosidebar']) {
            $conf['sidebar'] = '';
        }
    }
}
