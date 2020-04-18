<?php
/**
 * DokuWiki Plugin dropfiles (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Michael Große <dokuwiki@cosmocode.de>
 */

// must be run within Dokuwiki
if (!defined('DOKU_INC')) {
    die();
}

class action_plugin_dropfiles_jsinfoconfig extends DokuWiki_Action_Plugin
{


    /**
     * Registers a callback function for a given event
     *
     * @param Doku_Event_Handler $controller DokuWiki's event controller object
     *
     * @return void
     */
    public function register(Doku_Event_Handler $controller)
    {
        $controller->register_hook('DOKUWIKI_STARTED', 'BEFORE', $this, 'addDataToJSINFO');
    }

    /**
     * Make config settings available to javascript
     *
     * @param Doku_Event $event
     * @param $param
     */
    public function addDataToJSINFO(Doku_Event $event, $param) {
        global $JSINFO;

        if (!isset($JSINFO['plugins'])) {
            $JSINFO['plugins'] = [];
        }
        $JSINFO['plugins']['dropfiles'] = ['insertFileLink' => $this->getConf('insertFileLink')];
    }

}
