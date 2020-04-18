<?php
/**
 * ToDo Plugin: Creates a checkbox based todo list
 *
 * Syntax: <todo [@username] [#]>Name of Action</todo> -
 *  Creates a Checkbox with the "Name of Action" as
 *  the text associated with it. The hash (#, optional)
 *  will cause the checkbox to be checked by default.
 *  The @ sign followed by a username can be used to assign this todo to a user.
 *  examples:
 *     A todo without user assignment
 *       <todo>Something todo</todo>
 *     A completed todo without user assignment
 *       <todo #>Completed todo</todo>
 *     A todo assigned to user User
 *       <todo @leo>Something todo for Leo</todo>
 *     A completed todo assigned to user User
 *       <todo @leo #>Todo completed for Leo</todo>
 *
 * In combination with dokuwiki searchpattern plugin version (at least v20130408),
 * it is a lightweight solution for a task management system based on dokuwiki.
 * use this searchpattern expression for open todos:
 *     ~~SEARCHPATTERN#'/<todo[^#>]*>.*?<\/todo[\W]*?>/'?? _ToDo ??~~
 * use this searchpattern expression for completed todos:
 *     ~~SEARCHPATTERN#'/<todo[^#>]*#[^>]*>.*?<\/todo[\W]*?>/'?? _ToDo ??~~
 * do not forget the no-cache option
 *     ~~NOCACHE~~
 *
 * Compatibility:
 *     Release 2013-03-06 "Weatherwax RC1"
 *     Release 2012-10-13 "Adora Belle"
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Babbage <babbage@digitalbrink.com>; Leo Eibler <dokuwiki@sprossenwanne.at>
 */

/**
 * ChangeLog:
 *
 * [06/14/2014]: by Markus Gschwendt <markus@runout.at>
 *               add option usernames for <todo>
 *               add start/due-date filter to todolist
 *               bugfix: when the checkbox is clicked the arguments of the <todo> tag will be lost
 *               some minor bugfixes
 * [06/11/2014]: by Markus Gschwendt <markus@runout.at>
 *               add option showdate to show/hide start/due-date
 * [05/15/2014]: by Markus Gschwendt <markus@runout.at>
 *               multiple users in <todo>, only the first user will be shown in rendered output
 *               ! there is still a bug when the checkbox is clicked the arguments of the <todo> tag will be lost
 * [05/14/2014]: by Markus Gschwendt <markus@runout.at>
 *               add start-due date: set a start and/or due date and get colored output (css)
 *               clean up some code, so we have less variables in function calls, use arrays instead
 * [05/11/2014]: by Markus Gschwendt <markus@runout.at>
 *               add options for list rendering: username:user|real|none checkbox:yes|no header:id|firstheader
 ** [04/13/2013]: by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at
 **               bugfix: config option Strikethrough
 * [04/11/2013]: by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at
 *               bugfix: encoding html code (security risk <todo><script>alert('hi')</script></todo>) - bug reported by Andreas
 *               bugfix: use correct <todo> tag if there are more than 1 in the same line.
 * [04/08/2013]: by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at
 *               migrate changes made by Christian Marg to current version of plugin
 * [04/08/2013]: by Christian Marg <marg@rz.tu-clausthal.de>
 *               changed behaviour - when multiple todo-items have the same text, only the clicked one is checked.
 * [04/08/2013]: by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at
 *               add description / comments and syntax howto about integration with searchpattern
 *               check compatibility with dokuwiki release 2012-10-13 "Adora Belle"
 *               remove getInfo() call because it's done by plugin.info.txt (since dokuwiki 2009-12-25 "Lemming")
 * [04/07/2013]: by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at
 *               add handler method _searchpatternHandler() for dokuwiki searchpattern extension.
 *               add user assignment for todos (with @username syntax in todo tag e.g. <todo @leo>do something</todo>)
 * [04/05/2013]: by Leo Eibler <dokuwiki@sprossenwanne.at> / http://www.eibler.at
 *               upgrade plugin to work with newest version of dokuwiki (tested version Release 2013-03-06 Weatherwax RC1).
 * [08/16/2010]: Fixed another bug where javascript would not decode the action
 *               text properly (replaced unescape with decodeURIComponent).
 * [04/03/2010]: Fixed a bug where javascript would not decode the action text
 *               properly.
 * [03/31/2010]: Fixed a bug where checking or unchecking an action whose text
 *               appeared outside of the todo tags, would result in mangling the
 *               code on your page. Also added support for using the ampersand
 *               character (&) and html entities inside of your todo action.
 * [02/27/2010]: Created an action plugin to insert a ToDo button into the
 *               editor toolbar.
 * [10/14/2009]: Added the feature so that if you have Links turned off and you
 *               click on the text of an action, it will check that action off.
 *               Thanks to Tero for the suggestion! (Plugin Option: CheckboxText)
 * [10/08/2009]: I am no longer using the short open php tag (<?) for my
 *               ajax.php file. This was causing some problems for people who had
 *               short_open_tags=Off in their php.ini file (thanks Marcus!)
 * [10/01/2009]: Updated javascript to use .nextSibling instead of .nextElementSibling
 *               to make it compatible with older versions of Firefox and IE.
 * [09/13/2009]: Replaced ':' with a '-' in the action link so as not to create
 *               unnecessary namespaces (if the links option is active)
 * [09/10/2009]: Removed unnecessary function calls (urlencode) in _createLink() function
 * [09/09/2009]: Added ability for user to choose where Action links point to
 * [08/30/2009]: Initial Release
 */

if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_todo_todo extends DokuWiki_Syntax_Plugin {

    /**
     * Get the type of syntax this plugin defines.
     *
     * @return String
     */
    public function getType() {
        return 'substition';
    }

    /**
     * Paragraph Type
     *
     * 'normal' - The plugin can be used inside paragraphs
     * 'block'  - Open paragraphs need to be closed before plugin output
     * 'stack'  - Special case. Plugin wraps other paragraphs.
     */
    function getPType(){
        return 'normal';
    }

    /**
     * Where to sort in?
     *
     * @return Integer
     */
    public function getSort() {
        return 999;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param $mode String The desired rendermode.
     * @return void
     * @see render()
     */
    public function connectTo($mode) {
        $this->Lexer->addEntryPattern('<todo[\s]*?.*?>(?=.*?</todo>)', $mode, 'plugin_todo_todo');
        $this->Lexer->addSpecialPattern('~~NOTODO~~', $mode, 'plugin_todo_todo');
    }

    public function postConnect() {
        $this->Lexer->addExitPattern('</todo>', 'plugin_todo_todo');
    }

    /**
     * Handler to prepare matched data for the rendering process.
     *
     * @param $match    string  The text matched by the patterns.
     * @param $state    int     The lexer state for the match.
     * @param $pos      int     The character position of the matched text.
     * @param $handler Doku_Handler  Reference to the Doku_Handler object.
     * @return int The current lexer state for the match.
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        switch($state) {
            case DOKU_LEXER_ENTER :
                #Search to see if the '#' is in the todotag (if so, this means the Action has been completed)
                $x = preg_match('%<todo([^>]*)>%i', $match, $tododata);
                if($x) {
                    $handler->todoargs =  $this->parseTodoArgs($tododata[1]);
                }
                if(!is_numeric($handler->todo_index)) {
                    $handler->todo_index = 0;
                }
                break;
            case DOKU_LEXER_MATCHED :
                break;
            case DOKU_LEXER_UNMATCHED :
                /**
                 * Structure:
                 * input(checkbox)
                 * <span>
                 * -<a> (if links is on) or <span> (if links is off)
                 * --<del> (if strikethrough is on) or --NOTHING--
                 * -</a> or </span>
                 * </span>
                 */

                #Make sure there is actually an action to create
                if(trim($match) != '') {

                    $data = array_merge(array ($state, 'todotitle' => $match, 'todoindex' => $handler->todo_index, 'todouser' => $handler->todo_user, 'checked' => $handler->checked), $handler->todoargs);
                    $handler->todo_index++;
                    return $data;
                }

                break;
            case DOKU_LEXER_EXIT :
                #Delete temporary checked variable
                unset($handler->todo_user);
                unset($handler->checked);
                unset($handler->todoargs);
                //unset($handler->todo_index);
                break;
            case DOKU_LEXER_SPECIAL :
                break;
        }
        return array();
    }

    /**
     * Handle the actual output creation.
     *
     * @param  $mode     String        The output format to generate.
     * @param $renderer Doku_Renderer A reference to the renderer object.
     * @param  $data     Array         The data created by the <tt>handle()</tt> method.
     * @return Boolean true: if rendered successfully, or false: otherwise.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        global $ID;
        list($state, $todotitle) = $data;
        if($mode == 'xhtml') {
            /** @var $renderer Doku_Renderer_xhtml */
            if($state == DOKU_LEXER_UNMATCHED) {

                #Output our result
                $renderer->doc .= $this->createTodoItem($renderer, $ID, array_merge($data, array('checkbox'=>'yes')));
                return true;
            }

        } elseif($mode == 'metadata') {
            /** @var $renderer Doku_Renderer_metadata */
            if($state == DOKU_LEXER_UNMATCHED) {
                $id = $this->_composePageid($todotitle);
                $renderer->internallink($id, $todotitle);
            }
        }
        return false;
    }

    /**
     * Parse the arguments of todotag
     *
     * @param string $todoargs
     * @return array(bool, false|string) with checked and user
     */
    protected function parseTodoArgs($todoargs) {
        $data['checked'] = false;
        unset($data['start']);
        unset($data['due']);
        unset($data['completeddate']);
        $data['showdate'] = $this->getConf("ShowdateTag");
        $data['username'] = $this->getConf("Username");
        $options = explode(' ', $todoargs);
        foreach($options as $option) {
            $option = trim($option);
            if($option[0] == '@') {
                $data['todousers'][] = substr($option, 1); //fill todousers array
                if(!isset($data['todouser'])) $data['todouser'] = substr($option, 1); //set the first/main todouser
            }
            elseif($option[0] == '#') {
                $data['checked'] = true;
                @list($completeduser, $completeddate) = explode(':', $option, 2);
                $data['completeduser'] = substr($completeduser, 1);
                if(date('Y-m-d', strtotime($completeddate)) == $completeddate) {
                    $data['completeddate'] = new DateTime($completeddate);
                }
            }
            else {
                @list($key, $value) = explode(':', $option, 2);
                switch($key) {
                    case 'username':
                        if(in_array($value, array('user', 'real', 'none'))) {
                            $data['username'] = $value;
                        }
                        break;
                    case 'start':
                        if(date('Y-m-d', strtotime($value)) == $value) {
                            $data['start'] = new DateTime($value);
                        }
                        break;
                    case 'due':
                        if(date('Y-m-d', strtotime($value)) == $value) {
                            $data['due'] = new DateTime($value);
                        }
                        break;
                    case 'showdate':
                        if(in_array($value, array('yes', 'no'))) {
                            $data['showdate'] = ($value == 'yes');
                        }
                        break;
                }
            }
        }
        return $data;
    }

    /**
     * @param Doku_Renderer_xhtml $renderer
     * @param string $id of page
     * @param array  $data  data for rendering options
     * @return string html of an item
     */
    protected function createTodoItem($renderer, $id, $data) {
        //set correct context
        global $ID, $INFO;
        $oldID = $ID;
        $ID = $id;
        $todotitle = $data['todotitle'];
        $todoindex = $data['todoindex'];
        $todouser = $data['todousers'][0];
        $checked = $data['checked'];

        if($data['checkbox']) {
            $return = '<input type="checkbox" class="todocheckbox"'
            . ' data-index="' . $todoindex . '"'
            . ' data-date="' . hsc(@filemtime(wikiFN($ID))) . '"'
            . ' data-pageid="' . hsc($ID) . '"'
            . ' data-strikethrough="' . ($this->getConf("Strikethrough") ? '1' : '0') . '"'
            . ($checked ? ' checked="checked"' : '') . ' /> ';
        }

        // Username of first todouser in list
        if($todouser && $data['username'] != 'none') {
            switch ($data['username']) {
                case "user":
                    break;
                case "real":
                    global $auth;
                    $todouser = $auth->getUserData($todouser)['name'];
                    break;
                case "none": 
                    unset($todouser); 
                    break;
            }
            if($todouser) {
                $return .= '<span class="todouser">[' . hsc($todouser) . ']</span>';
            }
        }

        // start/due date
        unset($bg);
        $now = new DateTime("now");
        if(!$checked && (isset($data['start']) || isset($data['due'])) && (!isset($data['start']) || $data['start']<$now) && (!isset($data['due']) || $now<$data['due'])) $bg='todostarted';
        if(!$checked && isset($data['due']) && $now>=$data['due']) $bg='tododue';

        // show start/due date
        if($data['showdate'] == 1 && (isset($data['start']) || isset($data['due']))) {
            $return .= '<span class="tododates">[';
            if(isset($data['start'])) { $return .= $data['start']->format('Y-m-d'); }
            $return .= ' → ';
            if(isset($data['due'])) { $return .= $data['due']->format('Y-m-d'); }
            $return .= ']</span>';
        }

        $spanclass = 'todotext';
        if($this->getConf("CheckboxText") && !$this->getConf("AllowLinks") && $oldID == $ID && $data['checkbox']) {
            $spanclass .= ' clickabletodo todohlght';
        }
        if(isset($bg)) $spanclass .= ' '.$bg;
        $return .= '<span class="' . $spanclass . '">';

        if($checked && $this->getConf("Strikethrough")) {
            $return .= '<del>';
        }
        $return .= '<span class="todoinnertext">';
        if($this->getConf("AllowLinks")) {
            $return .= $this->_createLink($renderer, $todotitle, $todotitle);
        } else {
            if ($oldID != $ID) {
                $return .= $renderer->internallink($id, $todotitle, null, true);
            } else {
                 $return .= hsc($todotitle);
            }
        }
        $return .= '</span>';

        if($checked && $this->getConf("Strikethrough")) {
            $return .= '</del>';
        }

        $return .= '</span>';

        //restore page ID
        $ID = $oldID;
        return $return;
    }

    /**
     * Generate links from our Actions if necessary.
     *
     * @param Doku_Renderer_xhtml $renderer
     * @param string $pagename
     * @param string $name
     * @return string
     */
    private function _createLink($renderer, $pagename, $name = NULL) {
        $id = $this->_composePageid($pagename);

        return $renderer->internallink($id, $name, null, true);
    }

    /**
     * Compose the pageid of the pages linked by a todoitem
     *
     * @param string $pagename
     * @return string page id
     */
    private function _composePageid($pagename) {
        #Get the ActionNamespace and make sure it ends with a : (if not, add it)
        $actionNamespace = $this->getConf("ActionNamespace");
        if(strlen($actionNamespace) == 0 || substr($actionNamespace, -1) != ':') {
            $actionNamespace .= ":";
        }

        #Replace ':' in $pagename so we don't create unnecessary namespaces
        $pagename = str_replace(':', '-', $pagename);

        //resolve and build link
        $id = $actionNamespace. $pagename;
        return $id;
    }

    /**
     * @brief this function can be called by dokuwiki plugin searchpattern to process the todos found by searchpattern.
     * use this searchpattern expression for open todos:
     *          ~~SEARCHPATTERN#'/<todo[^#>]*>.*?<\/todo[\W]*?>/'?? _ToDo ??~~
     * use this searchpattern expression for completed todos:
     *          ~~SEARCHPATTERN#'/<todo[^#>]*#[^>]*>.*?<\/todo[\W]*?>/'?? _ToDo ??~~
     * this handler method uses the table and layout with css classes from searchpattern plugin
     *
     * @param $type   string type of the request from searchpattern plugin
     *                (wholeoutput, intable:whole, intable:prefix, intable:match, intable:count, intable:suffix)
     *                wholeoutput     = all output is done by THIS plugin (no output will be done by search pattern)
     *                intable:whole   = the left side of table (page name) is done by searchpattern, the right side
     *                                  of the table will be done by THIS plugin
     *                intable:prefix  = on the right side of table - THIS plugin will output a prefix header and
     *                                  searchpattern will continue it's default output
     *                intable:match   = if regex, right side of table - THIS plugin will format the current
     *                                  outputvalue ($value) and output it instead of searchpattern
     *                intable:count   = if normal, right side of table - THIS plugin will format the current
     *                                  outputvalue ($value) and output it instead of searchpattern
     *                intable:suffix  = on the right side of table - THIS plugin will output a suffix footer and
     *                                  searchpattern will continue it's default output
     * @param Doku_Renderer_xhtml $renderer current rendering object (use $renderer->doc .= 'text' to output text)
     * @param array $data     whole data multidemensional array( array( $page => $countOfMatches ), ... )
     * @param array $matches  whole regex matches multidemensional array( array( 0 => '1st Match', 1 => '2nd Match', ... ), ... )
     * @param string $page     id of current page
     * @param array $params   the parameters set by searchpattern (see search pattern documentation)
     * @param string $value    value which should be outputted by searchpattern
     * @return bool true if THIS method is responsible for the output (using $renderer->doc) OR false if searchpattern should output it's default
     */
    public function _searchpatternHandler($type, $renderer, $data, $matches, $params = array(), $page = null, $value = null) {
        $renderer->nocache();

        $type = strtolower($type);
        switch($type) {
            case 'wholeoutput':
                // $matches should hold an array with all <todo>matches</todo> or <todo #>matches</todo>
                if(!is_array($matches)) {
                    return false;
                }
                //file_put_contents( dirname(__FILE__).'/debug.txt', print_r($matches,true), FILE_APPEND );
                //file_put_contents( dirname(__FILE__).'/debug.txt', print_r($params,true), FILE_APPEND );
                $renderer->doc .= '<div class="sp_main">';
                $renderer->doc .= '<table class="inline sp_main_table">'; //create table

                foreach($matches as $page => $allTodosPerPage) {
                    $renderer->doc .= '<tr class="sp_title"><th class="sp_title" colspan="2"><a href="' . wl($page) . '">' . $page . '</a></td></tr>';
                    //entry 0 contains all whole matches
                    foreach($allTodosPerPage[0] as $todoindex => $todomatch) {
                        $x = preg_match('%<todo([^>]*)>(.*)</[\W]*todo[\W]*>%i', $todomatch, $tododata);

                        if($x) {
                            list($checked, $todouser) = $this->parseTodoArgs($tododata[1]);
                            $todotitle = trim($tododata[2]);
                            if(empty($todotitle)) {
                                continue;
                            }
                            $renderer->doc .= '<tr class="sp_result"><td class="sp_page" colspan="2">';

                            // in case of integration with searchpattern there is no chance to find the index of an element
                            $renderer->doc .= $this->createTodoItem($renderer, $todotitle, $todoindex, $todouser, $checked, $page, array('checkbox'=>'yes', 'username'=>'user'));

                            $renderer->doc .= '</td></tr>';
                        }
                    }
                }
                $renderer->doc .= '</table>'; //end table
                $renderer->doc .= '</div>';
                // true means, that this handler method does the output (searchpattern plugin has nothing to do)
                return true;
                break;
            case 'intable:whole':
                break;
            case 'intable:prefix':
                //$renderer->doc .= '<b>Start on Page '.$page.'</b>';
                break;
            case 'intable:match':
                //$renderer->doc .= 'regex match on page '.$page.': <pre>'.$value.'</pre>';
                break;
            case 'intable:count':
                //$renderer->doc .= 'normal count on page '.$page.': <pre>'.$value.'</pre>';
                break;
            case 'intable:suffix':
                //$renderer->doc .= '<b>End on Page '.$page.'</b>';
                break;
            default:
                break;
        }
        // false means, that this handler method does not output anything. all should be done by searchpattern plugin
        return false;
    }
}

//Setup VIM: ex: et ts=4 enc=utf-8 :
