<?php

/**
 * ToDo Action Plugin: Inserts button for ToDo plugin into toolbar
 *
 * Original Example: http://www.dokuwiki.org/devel:action_plugins
 * @author     Babbage <babbage@digitalbrink.com>
 * @date 20130405 Leo Eibler <dokuwiki@sprossenwanne.at> \n
 *                replace old sack() method with new jQuery method and use post instead of get \n
 * @date 20130408 Leo Eibler <dokuwiki@sprossenwanne.at> \n
 *                remove getInfo() call because it's done by plugin.info.txt (since dokuwiki 2009-12-25 Lemming)
 */

if(!defined('DOKU_INC')) die();
/**
 * Class action_plugin_todo registers actions
 */
class action_plugin_todo extends DokuWiki_Action_Plugin {

    /**
     * Register the eventhandlers
     */
    public function register(Doku_Event_Handler $controller) {
        $controller->register_hook('TOOLBAR_DEFINE', 'AFTER', $this, 'insert_button', array());
        $controller->register_hook('AJAX_CALL_UNKNOWN', 'BEFORE', $this, '_ajax_call', array());
    }

    /**
     * Inserts the toolbar button
     */
    public function insert_button(&$event, $param) {
        $event->data[] = array(
            'type' => 'format',
            'title' => $this->getLang('qb_todobutton'),
            'icon' => '../../plugins/todo/todo.png',
// key 't' is already used for going to top of page, bug #76
//	    'key' => 't',
            'open' => '<todo>',
            'close' => '</todo>',
            'block' => false,
        );
    }

    /**
     * Handles ajax requests for to do plugin
     *
     * @brief This method is called by ajax if the user clicks on the to-do checkbox or the to-do text.
     * It sets the to-do state to completed or reset it to open.
     *
     * POST Parameters:
     *   index    int the position of the occurrence of the input element (starting with 0 for first element/to-do)
     *   checked    int should the to-do set to completed (1) or to open (0)
     *   path    string id/path/name of the page
     *
     * @date 20140317 Leo Eibler <dokuwiki@sprossenwanne.at> \n
     *                use todo content as change description \n
     * @date 20131008 Gerrit Uitslag <klapinklapin@gmail.com> \n
     *                move ajax.php to action.php, added lock and conflict checks and improved saving
     * @date 20130405 Leo Eibler <dokuwiki@sprossenwanne.at> \n
     *                replace old sack() method with new jQuery method and use post instead of get \n
     * @date 20130407 Leo Eibler <dokuwiki@sprossenwanne.at> \n
     *                add user assignment for todos \n
     * @date 20130408 Christian Marg <marg@rz.tu-clausthal.de> \n
     *                change only the clicked to-do item instead of all items with the same text \n
     *                origVal is not used anymore, we use the index (occurrence) of input element \n
     * @date 20130408 Leo Eibler <dokuwiki@sprossenwanne.at> \n
     *                migrate changes made by Christian Marg to current version of plugin \n
     *
     *
     * @param Doku_Event $event
     * @param mixed $param not defined
     */
    public function _ajax_call(&$event, $param) {
        global $ID, $conf, $lang;

        if($event->data !== 'plugin_todo') {
            return;
        }
        //no other ajax call handlers needed
        $event->stopPropagation();
        $event->preventDefault();

        #Variables
        // by einhirn <marg@rz.tu-clausthal.de> determine checkbox index by using class 'todocheckbox'

        if(isset($_REQUEST['index'], $_REQUEST['checked'], $_REQUEST['pageid'])) {
            // index = position of occurrence of <input> element (starting with 0 for first element)
            $index = (int) $_REQUEST['index'];
            // checked = flag if input is checked means to do is complete (1) or not (0)
            $checked = (boolean) urldecode($_REQUEST['checked']);
            // path = page ID
            $ID = cleanID(urldecode($_REQUEST['pageid']));
        } else {
            return;
        }

        $date = 0;
        if(isset($_REQUEST['date'])) $date = (int) $_REQUEST['date'];

        $INFO = pageinfo();

        #Determine Permissions
        if(auth_quickaclcheck($ID) < AUTH_EDIT) {
            echo "You do not have permission to edit this file.\nAccess was denied.";
            return;
        }
        // Check, if page is locked
        if(checklock($ID)) {
            $locktime = filemtime(wikiLockFN($ID));
            $expire = dformat($locktime + $conf['locktime']);
            $min = round(($conf['locktime'] - (time() - $locktime)) / 60);

            $msg = $this->getLang('lockedpage').'
'.$lang['lockedby'] . ': ' . editorinfo($INFO['locked']) . '
' . $lang['lockexpire'] . ': ' . $expire . ' (' . $min . ' min)';
            $this->printJson(array('message' => $msg));
            return;
        }

        //conflict check
        if($date != 0 && $INFO['meta']['date']['modified'] > $date) {
            $this->printJson(array('message' => $this->getLang('refreshpage')));
            return;
        }

        #Retrieve Page Contents
        $wikitext = rawWiki($ID);

        #Determine position of tag
        if($index >= 0) {
            $index++;
            // index is only set on the current page with the todos
            // the occurances are counted, untill the index-th input is reached which is updated
            $todoTagStartPos = $this->_strnpos($wikitext, '<todo', $index);
            $todoTagEndPos = strpos($wikitext, '>', $todoTagStartPos) + 1;

            if($todoTagEndPos > $todoTagStartPos) {
                // @date 20140714 le add todo text to minorchange
                $todoTextEndPos = strpos( $wikitext, '</todo', $todoTagEndPos );
                $todoText = substr( $wikitext, $todoTagEndPos, $todoTextEndPos-$todoTagEndPos );
                // update text
                $oldTag = substr($wikitext, $todoTagStartPos, ($todoTagEndPos - $todoTagStartPos));
                $newTag = $this->_buildTodoTag($oldTag, $checked);
                $wikitext = substr_replace($wikitext, $newTag, $todoTagStartPos, ($todoTagEndPos - $todoTagStartPos));

                // save Update (Minor)
                lock($ID);
                // @date 20140714 le add todo text to minorchange, use different message for checked or unchecked
                saveWikiText($ID, $wikitext, $this->getLang($checked?'checkboxchange_on':'checkboxchange_off').': '.$todoText, $minoredit = true);
                unlock($ID);

                $return = array(
                    'date' => @filemtime(wikiFN($ID)),
                    'succeed' => true
                );
                $this->printJson($return);
            }
        }
    }

    /**
     * Encode and print an arbitrary variable into JSON format
     *
     * @param mixed $return
     */
    private function printJson($return) {
        $json = new JSON();
        echo $json->encode($return);
    }

    /**
     * @brief gets current to-do tag and returns a new one depending on checked
     * @param $todoTag    string current to-do tag e.g. <todo @user>
     * @param $checked    int check flag (todo completed=1, todo uncompleted=0)
     * @return string new to-do completed or uncompleted tag e.g. <todo @user #>
     */
    private function _buildTodoTag($todoTag, $checked) {
        $user = '';
        if($checked == 1) {
            if(!empty($_SERVER['REMOTE_USER'])) { $user = $_SERVER['REMOTE_USER']; }
            $newTag = preg_replace('/>/', ' #'.$user.':'.date('Y-m-d').'>', $todoTag);
        } else {
            $newTag = preg_replace('/[\s]*[#].*>/', '>', $todoTag);
        }
        return $newTag;
    }


    /**
     * Find position of $occurance-th $needle in haystack
     */
    private function _strnpos($haystack, $needle, $occurance, $pos = 0) {
        for($i = 1; $i <= $occurance; $i++) {
            $pos = strpos($haystack, $needle, $pos) + 1;
        }
        return $pos - 1;
    }
}
