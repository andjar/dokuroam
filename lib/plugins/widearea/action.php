<?php
/**
 * DokuWiki Plugin widearea (Action Component)
 *
 *  @license      GPL 2 (http://www.gnu.org/licenses/gpl.html)
 *  @author       Matthias Schulte <dokuwiki@lupo49.de>
 *  @version      2013-07-10
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

class action_plugin_widearea extends DokuWiki_Action_Plugin {

    function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TPL_METAHEADER_OUTPUT', 'BEFORE', $this, 'handle_tpl_metaheader', array());
    }

    function handle_tpl_metaheader(&$event, $param) {
        if(empty($event->data) || empty($event->data['meta'])) return;
        $key = count($event->data['link']);

        $css = array(
            "rel"  => "stylesheet",
            "type" => "text/css",
            "href" => DOKU_BASE."lib/plugins/widearea/widearea/widearea.css"
        );

        $event->data['link'][$key] = $css;
    }
}