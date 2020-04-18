<?php
/**
 * DokuWiki Plugin todo_list (Syntax Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * Class syntax_plugin_todo_list
 */
class syntax_plugin_todo_list extends syntax_plugin_todo_todo {

    /**
     * @return string Syntax mode type
     */
    public function getType() {
        return 'substition';
    }

    /**
     * @return string Paragraph type
     */
    public function getPType() {
        return 'block';
    }

    /**
     * @return int Sort order - Low numbers go before high numbers
     */
    public function getSort() {
        return 250;
    }

    /**
     * Connect lookup pattern to lexer.
     *
     * @param string $mode Parser mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('~~TODOLIST[^~]*~~', $mode, 'plugin_todo_list');
    }

    /**
     * Handle matches of the todolist syntax
     *
     * @param string $match The match of the syntax
     * @param int $state The state of the handler
     * @param int $pos The position in the document
     * @param Doku_Handler $handler The handler
     * @return array Data for the renderer
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {

        $options = substr($match, 10, -2); // strip markup
        $options = explode(' ', $options);
        $data = array(
            'header' => $this->getConf("Header"),
            'completed' => 'all',
            'assigned' => 'all',
            'completeduserlist' => 'all',
            'ns' => 'all',
            'showdate' => $this->getConf("ShowdateList"),
            'checkbox' => $this->getConf("Checkbox"),
            'username' => $this->getConf("Username"),
            'short' => false,
        );
        $allowedvalues = array('yes', 'no');
        foreach($options as $option) {
            @list($key, $value) = explode(':', $option, 2);
            switch($key) {
                case 'header': // how should the header be rendered?
                    if(in_array($value, array('id', 'firstheader', 'none'))) {
                        $data['header'] = $value;
                    }
                    break;
                case 'short':
                    if(in_array($value, $allowedvalues)) {
                        $data['short'] = ($value == 'yes');
                    }
                    break;
                case 'showdate':
                    if(in_array($value, $allowedvalues)) {
                        $data['showdate'] = ($value == 'yes');
                    }
                    break;
                case 'checkbox': // should checkbox be rendered?
                    if(in_array($value, $allowedvalues)) {
                        $data['checkbox'] = ($value == 'yes');
                    }
                    break;
                case 'completed':
                    if(in_array($value, $allowedvalues)) {
                        $data['completed'] = ($value == 'yes');
                    }
                    break;
                case 'username': // how should the username be rendered?
                    if(in_array($value, array('user', 'real', 'none'))) {
                        $data['username'] = $value;
                    }
                    break;
                case 'assigned':
                    if(in_array($value, $allowedvalues)) {
                        $data['assigned'] = ($value == 'yes');
                        break;
                    }
                    //assigned?
                    $data['assigned'] = explode(',', $value);
                    // @date 20140317 le: if check for logged in user, also check for logged in user email address
                    if( in_array( '@@USER@@', $data['assigned'] ) ) {
                        $data['assigned'][] = '@@MAIL@@';
                    }
                    $data['assigned'] = array_map( array($this,"__todolistTrimUser"), $data['assigned'] );
                    break;
                case 'completeduser':
                    $data['completeduserlist'] = explode(',', $value);
                                        // @date 20140317 le: if check for logged in user, also check for logged in user email address
                                        if(in_array('@@USER@@', $data['completeduserlist'])) {
                                                $data['completeduserlist'][] = '@@MAIL@@';
                                        }
                    $data['completeduserlist'] = array_map( array($this,"__todolistTrimUser"), $data['completeduserlist'] );
                    break;
                case 'ns':
                    $data['ns'] = $value;
                    break;
                case 'startbefore':
                    list($data['startbefore'], $data['startignore']) = $this->analyseDate($value);
                    break;
                case 'startafter':
                    list($data['startafter'], $data['startignore']) = $this->analyseDate($value);
                    break;
                case 'startat':
                    list($data['startat'], $data['startignore']) = $this->analyseDate($value);
                    break;
                case 'duebefore':
                    list($data['duebefore'], $data['dueignore']) = $this->analyseDate($value);
                    break;
                case 'dueafter':
                    list($data['dueafter'], $data['dueignore']) = $this->analyseDate($value);
                    break;
                case 'dueat':
                    list($data['dueat'], $data['dueignore']) = $this->analyseDate($value);
                    break;
                case 'completedbefore':
                    list($data['completedbefore']) = $this->analyseDate($value);
                    break;
                case 'completedafter':
                    list($data['completedafter']) = $this->analyseDate($value);
                    break;
                case 'completedat':
                    list($data['completedat']) = $this->analyseDate($value);
                    break;
             }
        }
        return $data;
    }

    /**
     * Render xhtml output or metadata
     *
     * @param string $mode Renderer mode (supported modes: xhtml)
     * @param Doku_Renderer $renderer The renderer
     * @param array $data The data from the handler() function
     * @return bool If rendering was successful.
     */
    public function render($mode, Doku_Renderer $renderer, $data) {
        global $conf;

        if($mode != 'xhtml') return false;
        /** @var Doku_Renderer_xhtml $renderer */

        $opts['pattern'] = '/<todo([^>]*)>(.*)<\/todo[\W]*?>/'; //all todos in a wiki page
        $opts['ns'] = $data['ns'];
        //TODO check if storing subpatterns doesn't cost too much resources

        // search(&$data, $base,            $func,                       $opts,$dir='',$lvl=1,$sort='natural')
        search($todopages, $conf['datadir'], array($this, 'search_todos'), $opts); //browse wiki pages with callback to search_pattern

        $todopages = $this->filterpages($todopages, $data);

        if($data['short']) {
            $this->htmlShort($renderer, $todopages, $data);
        } else {
            $this->htmlTodoTable($renderer, $todopages, $data);
        }

        return true;
    }

    /**
     * Custom search callback
     *
     * This function is called for every found file or
     * directory. When a directory is given to the function it has to
     * decide if this directory should be traversed (true) or not (false).
     * Return values for files are ignored
     *
     * All functions should check the ACL for document READ rights
     * namespaces (directories) are NOT checked (when sneaky_index is 0) as this
     * would break the recursion (You can have an nonreadable dir over a readable
     * one deeper nested) also make sure to check the file type (for example
     * in case of lockfiles).
     *
     * @param array &$data  - Reference to the result data structure
     * @param string $base  - Base usually $conf['datadir']
     * @param string $file  - current file or directory relative to $base
     * @param string $type  - Type either 'd' for directory or 'f' for file
     * @param int    $lvl   - Current recursion depht
     * @param array  $opts  - option array as given to search()
     * @return bool if this directory should be traversed (true) or not (false). Return values for files are ignored.
     */
    public function search_todos(&$data, $base, $file, $type, $lvl, $opts) {
        $item['id'] = pathID($file); //get current file ID

        //we do nothing with directories
        if($type == 'd') return true;

        //only search txt files
        if(substr($file, -4) != '.txt') return true;

        //check ACL
        if(auth_quickaclcheck($item['id']) < AUTH_READ) return false;

        // filter namespaces
        if(!$this->filter_ns($item['id'], $opts['ns'])) return false;

        $wikitext = rawWiki($item['id']); //get wiki text

        // check if ~~NOTODO~~ is set on the page to skip this page
        if(1 == preg_match('/~~NOTODO~~/', $wikitext)) return false;

        $item['count'] = preg_match_all($opts['pattern'], $wikitext, $matches); //count how many times appears the pattern
        if(!empty($item['count'])) { //if it appears at least once
            $item['matches'] = $matches;
            $data[] = $item;
        }
        return true;
    }

    /**
     * filter namespaces
     *
     * @param $todopages array pages with all todoitems
     * @param $item     string listing parameters
     * @return boolean if item id is in namespace
     */
    private function filter_ns($item, $ns) {
        global $ID;
        // check if we should accept currant namespace+subnamespaces or only subnamespaces
        $wildsubns = substr($ns, -2) == '.:';
        $onlysubns = !$wildsubns && (substr($ns, -1) == ':' || substr($ns, -2) == ':.');
//        $onlyns =  $onlysubns && substr($ns, -1) == '.';

        // if first char of ns is '.'replace it with current ns
        if ($ns[0] == '.') {
            $ns = substr($ID, 0, strrpos($ID, ':')+1).ltrim($ns, '.:');
        }
        $ns = trim($ns, '.:');
        $len = strlen($ns);
        $parsepage = false;

        if ($parsepage = $ns == 'all') {
            // Always return the todo pages
        } elseif ($ns == '/') {
            // Only return the todo page if it's in the root namespace 
            $parsepage = strpos($item, ':') === FALSE;
        } elseif ($wildsubns) {
            $p = strpos($item.':', ':', $len+1);
            $x = substr($item, $len+1, $p-$len);
            $parsepage = 0 === strpos($item, rtrim($ns.':'.$x, ':').':');
        } elseif ($onlysubns) {
            $parsepage = 0 === strpos($item, $ns.':');
        } elseif ($parsepage = substr($item, 0, $len) == $ns) {
        }
        return $parsepage;
    }

    /**
     * Expand assignee-placeholders 
     * 
     * @param $user	String to be worked on
     * @return	expanded string
     */
    private function __todolistExpandAssignees($user) {
        global $USERINFO;
        if($user == '@@USER@@' && !empty($_SERVER['REMOTE_USER'])) {  //$INPUT->server->str('REMOTE_USER')
            return $_SERVER['REMOTE_USER'];
        }
        // @date 20140317 le: check for logged in user email address
        if( $user == '@@MAIL@@' && isset( $USERINFO['mail'] ) ) {  
            return $USERINFO['mail'];
        }
        return  $user;
    }

    /**
     * Trim input if it's a user
     * 
     * @param $user	String to be worked on
     * @return	trimmed string
     */
    private function __todolistTrimUser($user) {
        //placeholder (inspired by replacement-patterns - see https://www.dokuwiki.org/namespace_templates#replacement_patterns)
        if( $user == '@@USER@@' || $user == '@@MAIL@@' ) {
            return $user;
        }
        //user
        return trim(ltrim($user, '@'));
    }

    /**
     * filter the pages
     *
     * @param $todopages array pages with all todoitems
     * @param $data      array listing parameters
     * @return array filtered pages
     */
    private function filterpages($todopages, $data) {
        $pages = array();
        if(count($todopages)>0) {
            foreach($todopages as $page) {
                $todos = array();
                // contains 3 arrays: an array with complete matches and 2 arrays with subpatterns
                foreach($page['matches'][1] as $todoindex => $todomatch) {
                    $todo = array_merge(array('todotitle' => trim($page['matches'][2][$todoindex]),  'todoindex' => $todoindex), $this->parseTodoArgs($todomatch), $data);

                    if($this->isRequestedTodo($todo)) { $todos[] = $todo; }
                }
                if(count($todos) > 0) {
                    $pages[] = array('id' => $page['id'], 'todos' => $todos);
                }
            }
            return $pages;
        }
    return null;
    }


    private function htmlShort($R, $todopages, $data) {
        $done = 0; $todo = 0;
        foreach($todopages as $page) {
            foreach($page['todos'] as $value) {
                $todo++;
                if ($value['checked']) {
                    $done++;
                }
            }
            return $pages;
        }

        $R->cdata("($done/$todo)");
    }

    /**
     * Create html for table with todos
     *
     * @param Doku_Renderer_xhtml $R
     * @param array $todopages
     * @param array $data array with rendering options
     */
    private function htmlTodoTable($R, $todopages, $data) {
        $R->table_open();
        foreach($todopages as $page) {
       	    if ($data['header']!='none') {
                $R->tablerow_open();
                $R->tableheader_open();
                $R->internallink(':'.$page['id'], ($data['header']=='firstheader' ? p_get_first_heading($page['id']) : $page['id']));
                $R->tableheader_close();
                $R->tablerow_close();
       	    }
            foreach($page['todos'] as $todo) {
//echo "<pre>";var_dump($todo);echo "</pre>";
                $R->tablerow_open();
                $R->tablecell_open();
                $R->doc .= $this->createTodoItem($R, $page['id'], array_merge($todo, $data));
                $R->tablecell_close();
                $R->tablerow_close();
            }
        }
        $R->table_close();
    }

    /**
     * Check the conditions for adding a todoitem
     *
     * @param $data     array the defined filters
     * @param $checked  bool completion status of task; true: finished, false: open
     * @param $todouser string user username of user
     * @return bool if the todoitem should be listed
     */
    private function isRequestedTodo($data) {
        //completion status
        $condition1 = $data['completed'] === 'all' //all
                      || $data['completed'] === $data['checked']; //yes or no

        // resolve placeholder in assignees
        $requestedassignees = array();
        if(is_array($data['assigned'])) {
            $requestedassignees = array_map( array($this,"__todolistExpandAssignees"), $data['assigned'] );
        }
        //assigned
        $condition2 = $condition2
                        || $data['assigned'] === 'all' //all
                        || (is_bool($data['assigned']) && $data['assigned'] == $data['todouser']); //yes or no

        if (!$condition2 && is_array($data['assigned']) && is_array($data['todousers']))
            foreach($data['todousers'] as $todouser) {
                if(in_array($todouser, $requestedassignees)) { $condition2 = true; break; }
            }

        //completed by
        if($condition2 && is_array($data['completeduserlist']))
            $condition2 = in_array($data['completeduser'], $data['completeduserlist']);

        //compare start/due dates
        if($condition1 && $condition2) {
            $condition3s = true; $condition3d = true;
            if(isset($data['startbefore']) || isset($data['startafter']) || isset($data['startat'])) {
                if(is_object($data['start'])) {
                    if($data['startignore'] != '!') {
                        if(isset($data['startbefore'])) { $condition3s = $condition3s && new DateTime($data['startbefore']) > $data['start']; }
                        if(isset($data['startafter'])) { $condition3s = $condition3s && new DateTime($data['startafter']) < $data['start']; }
                        if(isset($data['startat'])) { $condition3s = $condition3s && new DateTime($data['startat']) == $data['start']; }
                    }
                } else {
                    if(!$data['startignore'] == '*') { $condition3s = false; }
                    if($data['startignore'] == '!') { $condition3s = false; }
                }
            }

            if(isset($data['duebefore']) || isset($data['dueafter']) || isset($data['dueat'])) {
                if(is_object($data['due'])) {
                    if($data['dueignore'] != '!') {
                        if(isset($data['duebefore'])) { $condition3d = $condition3d && new DateTime($data['duebefore']) > $data['due']; }
                        if(isset($data['dueafter'])) { $condition3d = $condition3d && new DateTime($data['dueafter']) < $data['due']; }
                        if(isset($data['dueat'])) { $condition3d = $condition3d && new DateTime($data['dueat']) == $data['due']; }
                    }
                 } else {
                    if(!$data['dueignore'] == '*') { $condition3d = false; }
                    if($data['dueignore'] == '!') { $condition3d = false; }
                }
            }
            $condition3 = $condition3s && $condition3d;
        }

        // compare completed date
        $condition4 = true;
        if(isset($data['completedbefore'])) {
            $condition4 = $condition4 && new DateTime($data['completedbefore']) > $data['completeddate'];
        }
        if(isset($data['completedafter'])) {
            $condition4 = $condition4 && new DateTime($data['completedafter']) < $data['completeddate'];
        }
        if(isset($data['completedat'])) {
            $condition4 = $condition4 && new DateTime($data['completedat']) == $data['completeddate'];
        }

        return $condition1 AND $condition2 AND $condition3 AND $condition4;
    }


    /**
    * Analyse of relative/absolute Date and return an absolute date
    *
    * @param $date      string  absolute/relative value of the date to analyse
    * @return           array   absolute date or actual date if $date is invalid
    */
    private function analyseDate($date) {
        $result = array($date, '');
        if(is_string($date)) {
            if($date == '!') {
               $result = array('', '!');
            } elseif ($date =='*') {
               $result = array('', '*');
            } else {
                if(substr($date, -1) == '*') {
                    $date = substr($date, 0, -1);
                    $result = array($date, '*');
                }

                if(date('Y-m-d', strtotime($date)) == $date) {
                    $result[0] = $date;
                } elseif(preg_match('/^[\+\-]\d+$/', $date)) { // check if we have a valid relative value
                    $newdate = date_create(date('Y-m-d'));
                    date_modify($newdate, $date . ' day');
                    $result[0] = date_format($newdate, 'Y-m-d');
                } else {
                    $result[0] = date('Y-m-d');
                }
            }
        } else { $result[0] = date('Y-m-d'); }

        return $result;
    }


}
