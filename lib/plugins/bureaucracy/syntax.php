<?php
/**
 * Bureaucracy Plugin: Allows flexible creation of forms
 *
 * This plugin allows definition of forms in wiki pages. The forms can be
 * submitted via email or used to create new pages from templates.
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Andreas Gohr <andi@splitbrain.org>
 * @author     Adrian Lang <dokuwiki@cosmocode.de>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

/**
 * All DokuWiki plugins to extend the parser/rendering mechanism
 * need to inherit from this class
 */
class syntax_plugin_bureaucracy extends DokuWiki_Syntax_Plugin {

    private $form_id = 0;
    var $patterns = array();
    var $values = array();
    var $noreplace = null;
    var $functions = array();

    /**
     * Prepare some replacements
     */
    public function __construct() {
        $this->prepareDateTimereplacements();
        $this->prepareNamespacetemplateReplacements();
        $this->prepareFunctions();
    }

    /**
     * What kind of syntax are we?
     */
    public function getType() {
        return 'substition';
    }

    /**
     * What about paragraphs?
     */
    public function getPType() {
        return 'block';
    }

    /**
     * Where to sort in?
     */
    public function getSort() {
        return 155;
    }

    /**
     * Connect pattern to lexer
     *
     * @param string $mode
     */
    public function connectTo($mode) {
        $this->Lexer->addSpecialPattern('<form>.*?</form>', $mode, 'plugin_bureaucracy');
    }

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  bool|array Return an array with all data you want to use in render, false don't add an instruction
     */
    public function handle($match, $state, $pos, Doku_Handler $handler) {
        $match = substr($match, 6, -7); // remove form wrap
        $lines = explode("\n", $match);
        $actions = $rawactions = array();
        $thanks = '';
        $labels = '';

        // parse the lines into an command/argument array
        $cmds = array();
        while(count($lines) > 0) {
            $line = trim(array_shift($lines));
            if(!$line) continue;
            $args = $this->_parse_line($line, $lines);
            $args[0] = $this->_sanitizeClassName($args[0]);

            if(in_array($args[0], array('action', 'thanks', 'labels'))) {
                if(count($args) < 2) {
                    msg(sprintf($this->getLang('e_missingargs'), hsc($args[0]), hsc($args[1])), -1);
                    continue;
                }

                // is action element?
                if($args[0] == 'action') {
                    array_shift($args);
                    $rawactions[] = array('type' => array_shift($args), 'argv' => $args);
                    continue;
                }

                // is thank you text?
                if($args[0] == 'thanks') {
                    $thanks = $args[1];
                    continue;
                }

                // is labels?
                if($args[0] == 'labels') {
                    $labels = $args[1];
                    continue;
                }
            }

            if(strpos($args[0], '_') === false) {
                $name = 'bureaucracy_field' . $args[0];
            } else {
                //name convention: plugin_componentname
                $name = $args[0];
            }

            /** @var helper_plugin_bureaucracy_field $field */
            $field = $this->loadHelper($name, false);
            if($field && is_a($field, 'helper_plugin_bureaucracy_field')) {
                $field->initialize($args);
                $cmds[] = $field;
            } else {
                $evdata = array('fields' => &$cmds, 'args' => $args);
                $event = new Doku_Event('PLUGIN_BUREAUCRACY_FIELD_UNKNOWN', $evdata);
                if($event->advise_before()) {
                    msg(sprintf($this->getLang('e_unknowntype'), hsc($name)), -1);
                }
            }

        }

        // check if action is available
        foreach($rawactions as $action) {
            $action['type'] = $this->_sanitizeClassName($action['type']);

            if(strpos($action['type'], '_') === false) {
                $action['actionname'] = 'bureaucracy_action' . $action['type'];
            } else {
                //name convention for other plugins: plugin_componentname
                $action['actionname'] = $action['type'];
            }

            list($plugin, $component) = explode('_', $action['actionname']);
            $alternativename = $action['type'] . '_'. $action['type'];

            // bureaucracy_action<name> or <plugin>_<componentname>
            if(!plugin_isdisabled($action['actionname']) || @file_exists(DOKU_PLUGIN . $plugin . '/helper/'  . $component . '.php')) {
                $actions[] = $action;

            // shortcut for other plugins with component name <name>_<name>
            } elseif(plugin_isdisabled($alternativename) || !@file_exists(DOKU_PLUGIN . $action['type'] . '/helper/'  . $action['type'] . '.php')) {
                $action['actionname'] = $alternativename;
                $actions[] = $action;

            // not found
            } else {
                $evdata = array('actions' => &$actions, 'action' => $action);
                $event = new Doku_Event('PLUGIN_BUREAUCRACY_ACTION_UNKNOWN', $evdata);
                if($event->advise_before()) {
                    msg(sprintf($this->getLang('e_unknownaction'), hsc($action['actionname'])), -1);
                }
            }
        }

        // action(s) found?
        if(count($actions) < 1) {
            msg($this->getLang('e_noaction'), -1);
        }

        // set thank you message
        if(!$thanks) {
            $thanks = "";
            foreach($actions as $action) {
                $thanks .= $this->getLang($action['type'] . '_thanks');
            }
        } else {
            $thanks = hsc($thanks);
        }
        return array(
            'fields'  => $cmds,
            'actions' => $actions,
            'thanks'  => $thanks,
            'labels'  => $labels
        );
    }

    /**
     * Handles the actual output creation.
     *
     * @param string          $format   output format being rendered
     * @param Doku_Renderer   $R        the current renderer object
     * @param array           $data     data created by handler()
     * @return  boolean                 rendered correctly? (however, returned value is not used at the moment)
     */
    public function render($format, Doku_Renderer $R, $data) {
        if($format != 'xhtml') return false;
        $R->info['cache'] = false; // don't cache

        /**
         * replace some time and name placeholders in the default values
         * @var $field helper_plugin_bureaucracy_field
         */
        foreach($data['fields'] as &$field) {
            if(isset($field->opt['value'])) {
                $field->opt['value'] = $this->replace($field->opt['value']);
            }
        }

        if($data['labels']) $this->loadlabels($data);

        $this->form_id++;
        if(isset($_POST['bureaucracy']) && checkSecurityToken() && $_POST['bureaucracy']['$$id'] == $this->form_id) {
            $success = $this->_handlepost($data);
            if($success !== false) {
                $R->doc .= '<div class="bureaucracy__plugin" id="scroll__here">' . $success . '</div>';
                return true;
            }
        }

        $R->doc .= $this->_htmlform($data['fields']);

        return true;
    }

    /**
     * Initializes the labels, loaded from a defined labelpage
     *
     * @param array $data all data passed to render()
     */
    protected function loadlabels(&$data) {
        global $INFO;
        $labelpage = $data['labels'];
        $exists = false;
        resolve_pageid($INFO['namespace'], $labelpage, $exists);
        if(!$exists) {
            msg(sprintf($this->getLang('e_labelpage'), html_wikilink($labelpage)), -1);
            return;
        }

        // parse simple list (first level cdata only)
        $labels = array();
        $instructions = p_cached_instructions(wikiFN($labelpage));
        $inli = 0;
        $item = '';
        foreach($instructions as $instruction) {
            if($instruction[0] == 'listitem_open') {
                $inli++;
                continue;
            }
            if($inli === 1 && $instruction[0] == 'cdata') {
                $item .= $instruction[1][0];
            }
            if($instruction[0] == 'listitem_close') {
                $inli--;
                if($inli === 0) {
                    list($k, $v) = explode('=', $item, 2);
                    $k = trim($k);
                    $v = trim($v);
                    if($k && $v) $labels[$k] = $v;
                    $item = '';
                }
            }
        }

        // apply labels to all fields
        $len = count($data['fields']);
        for($i = 0; $i < $len; $i++) {
            if(isset($data['fields'][$i]->depends_on)) {
                // translate dependency on fieldsets
                $label = $data['fields'][$i]->depends_on[0];
                if(isset($labels[$label])) {
                    $data['fields'][$i]->depends_on[0] = $labels[$label];
                }

            } else if(isset($data['fields'][$i]->opt['label'])) {
                // translate field labels
                $label = $data['fields'][$i]->opt['label'];
                if(isset($labels[$label])) {
                    $data['fields'][$i]->opt['display'] = $labels[$label];
                }
            }
        }

        if(isset($data['thanks'])) {
            if(isset($labels[$data['thanks']])) {
                $data['thanks'] = $labels[$data['thanks']];
            }
        }

    }

    /**
     * Validate posted data, perform action(s)
     *
     * @param array $data all data passed to render()
     * @return bool|string
     *      returns thanks message when fields validated and performed the action(s) succesfully;
     *      otherwise returns false.
     */
    private function _handlepost($data) {
        $success = true;
        foreach($data['fields'] as $index => $field) {
            /** @var $field helper_plugin_bureaucracy_field */

            $isValid = true;
            if($field->getFieldType() === 'file') {
                $file = array();
                foreach($_FILES['bureaucracy'] as $key => $value) {
                    $file[$key] = $value[$index];
                }
                $isValid = $field->handle_post($file, $data['fields'], $index, $this->form_id);

            } elseif($field->getFieldType() === 'fieldset' || !$field->hidden) {
                $isValid = $field->handle_post($_POST['bureaucracy'][$index], $data['fields'], $index, $this->form_id);
            }

            if(!$isValid) {
                // Do not return instantly to allow validation of all fields.
                $success = false;
            }
        }
        if(!$success) {
            return false;
        }

        $thanks_array = array();

        foreach($data['actions'] as $actionData) {
            /** @var helper_plugin_bureaucracy_action $action */
            $action = $this->loadHelper($actionData['actionname'], false);

            // action helper found?
            if(!$action) {
                msg(sprintf($this->getLang('e_unknownaction'), hsc($actionData['actionname'])), -1);
                return false;
            }

            try {
                $thanks_array[] = $action->run(
                    $data['fields'],
                    $data['thanks'],
                    $actionData['argv']
                );
            } catch(Exception $e) {
                msg($e->getMessage(), -1);
                return false;
            }
        }

        // Perform after_action hooks
        foreach($data['fields'] as $field) {
            $field->after_action();
        }

		// create thanks string
		$thanks = implode('', array_unique($thanks_array));

        return $thanks;
    }

    /**
     * Create the form
     *
     * @param helper_plugin_bureaucracy_field[] $fields array with form fields
     * @return string html of the form
     */
    private function _htmlform($fields) {
        global $ID;

        $form = new Doku_Form(array('class'   => 'bureaucracy__plugin',
                                    'id'      => 'bureaucracy__plugin' . $this->form_id,
                                    'enctype' => 'multipart/form-data'));
        $form->addHidden('id', $ID);
        $form->addHidden('bureaucracy[$$id]', $this->form_id);

        foreach($fields as $id => $field) {
            $field->renderfield(array('name' => 'bureaucracy[' . $id . ']'), $form, $this->form_id);
        }

        return $form->getForm();
    }

    /**
     * Parse a line into (quoted) arguments
     * Splits line at spaces, except when quoted
     *
     * @author William Fletcher <wfletcher@applestone.co.za>
     *
     * @param string $line line to parse
     * @param array  $lines all remaining lines
     * @return array with all the arguments
     */
    private function _parse_line($line, &$lines) {
        $args = array();
        $inQuote = false;
        $escapedQuote = false;
        $arg = '';
        do {
            $len = strlen($line);
            for($i = 0; $i < $len; $i++) {
                if($line[$i] == '"') {
                    if($inQuote) {
                        if($escapedQuote) {
                            $arg .= '"';
                            $escapedQuote = false;
                            continue;
                        }
                        if($line[$i + 1] == '"') {
                            $escapedQuote = true;
                            continue;
                        }
                        array_push($args, $arg);
                        $inQuote = false;
                        $arg = '';
                        continue;
                    } else {
                        $inQuote = true;
                        continue;
                    }
                } else if($line[$i] == ' ') {
                    if($inQuote) {
                        $arg .= ' ';
                        continue;
                    } else {
                        if(strlen($arg) < 1) continue;
                        array_push($args, $arg);
                        $arg = '';
                        continue;
                    }
                }
                $arg .= $line[$i];
            }
            if(!$inQuote || count($lines) === 0) break;
            $line = array_shift($lines);
            $arg .= "\n";
        } while(true);
        if(strlen($arg) > 0) array_push($args, $arg);
        return $args;
    }

    /**
     * Clean class name
     *
     * @param string $classname
     * @return string cleaned name
     */
    private function _sanitizeClassName($classname) {
        return preg_replace('/[^\w\x7f-\xff]/', '', strtolower($classname));
    }

    /**
     * Save content in <noreplace> tags into $this->noreplace
     *
     * @param string $input    The text to work on
     */
    protected function noreplace_save($input) {
        $pattern = '/<noreplace>(.*?)<\/noreplace>/is';
        //save content of <noreplace> tags
        preg_match_all($pattern, $input, $matches);
        $this->noreplace = $matches[1];
    }

    /**
     * Apply replacement patterns and values as prepared earlier
     * (disable $strftime to prevent double replacements with default strftime() replacements in nstemplate)
     *
     * @param string $input    The text to work on
     * @param bool   $strftime Apply strftime() replacements
     * @return string processed text
     */
    function replace($input, $strftime = true) {
        //in helper_plugin_struct_field::setVal $input can be an array
        //just return $input in that case
        if (!is_string($input)) return $input;
        if (is_null($this->noreplace)) $this->noreplace_save($input);

        foreach ($this->values as $label => $value) {
            $pattern = $this->patterns[$label];
            if (is_callable($value)) {
                $input = preg_replace_callback(
                    $pattern,
                    $value,
                    $input
                );
            } else {
                $input = preg_replace($pattern, $value, $input);
            }

        }

        if($strftime) {
            $input = preg_replace_callback(
                '/%./',
                function($m){return strftime($m[0]);},
                $input
            );
        }
        // user syntax: %%.(.*?)
        // strftime() is already applied once, so syntax is at this point: %.(.*?)
        $input = preg_replace_callback(
            '/@DATE\((.*?)(?:,\s*(.*?))?\)@/',
            array($this, 'replacedate'),
            $input
        );

        //run functions
        foreach ($this->functions as $name => $callback) {
            $pattern = '/@' . preg_quote($name) . '\((.*?)\)@/';
            if (is_callable($callback)) {
                $input = preg_replace_callback($pattern, function ($matches) use ($callback) {
                    return call_user_func($callback, $matches[1]);
                }, $input);
            }
        }

        //replace <noreplace> tags with their original content
        $pattern = '/<noreplace>.*?<\/noreplace>/is';
        if (is_array($this->noreplace)) foreach ($this->noreplace as $nr) {
            $input = preg_replace($pattern, $nr, $input, 1);
        }

        return $input;
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
     * Same replacements as applied at template namespaces
     *
     * @see parsePageTemplate()
     */
    function prepareNamespacetemplateReplacements() {
        /* @var Input $INPUT */
        global $INPUT;
        global $USERINFO;
        global $conf;
        global $ID;

        $this->patterns['__formpage_id__'] = '/@FORMPAGE_ID@/';
        $this->patterns['__formpage_ns__'] = '/@FORMPAGE_NS@/';
        $this->patterns['__formpage_curns__'] = '/@FORMPAGE_CURNS@/';
        $this->patterns['__formpage_file__'] = '/@FORMPAGE_FILE@/';
        $this->patterns['__formpage_!file__'] = '/@FORMPAGE_!FILE@/';
        $this->patterns['__formpage_!file!__'] = '/@FORMPAGE_!FILE!@/';
        $this->patterns['__formpage_page__'] = '/@FORMPAGE_PAGE@/';
        $this->patterns['__formpage_!page__'] = '/@FORMPAGE_!PAGE@/';
        $this->patterns['__formpage_!!page__'] = '/@FORMPAGE_!!PAGE@/';
        $this->patterns['__formpage_!page!__'] = '/@FORMPAGE_!PAGE!@/';
        $this->patterns['__user__'] = '/@USER@/';
        $this->patterns['__name__'] = '/@NAME@/';
        $this->patterns['__mail__'] = '/@MAIL@/';
        $this->patterns['__date__'] = '/@DATE@/';

        // replace placeholders
        $file = noNS($ID);
        $page = strtr($file, $conf['sepchar'], ' ');
        $this->values['__formpage_id__'] = $ID;
        $this->values['__formpage_ns__'] = getNS($ID);
        $this->values['__formpage_curns__'] = curNS($ID);
        $this->values['__formpage_file__'] = $file;
        $this->values['__formpage_!file__'] = utf8_ucfirst($file);
        $this->values['__formpage_!file!__'] = utf8_strtoupper($file);
        $this->values['__formpage_page__'] = $page;
        $this->values['__formpage_!page__'] = utf8_ucfirst($page);
        $this->values['__formpage_!!page__'] = utf8_ucwords($page);
        $this->values['__formpage_!page!__'] = utf8_strtoupper($page);
        $this->values['__user__'] = $INPUT->server->str('REMOTE_USER');
        $this->values['__name__'] = $USERINFO['name'];
        $this->values['__mail__'] = $USERINFO['mail'];
        $this->values['__date__'] = strftime($conf['dformat']);
    }

    /**
     * Date time replacements
     */
    function prepareDateTimereplacements() {
        $this->patterns['__year__'] = '/@YEAR@/';
        $this->patterns['__month__'] = '/@MONTH@/';
        $this->patterns['__monthname__'] = '/@MONTHNAME@/';
        $this->patterns['__day__'] = '/@DAY@/';
        $this->patterns['__time__'] = '/@TIME@/';
        $this->patterns['__timesec__'] = '/@TIMESEC@/';
        $this->values['__year__'] = date('Y');
        $this->values['__month__'] = date('m');
        $this->values['__monthname__'] = date('B');
        $this->values['__day__'] = date('d');
        $this->values['__time__'] = date('H:i');
        $this->values['__timesec__'] = date('H:i:s');

    }

    /**
     * Functions that can be used after replacements
     */
    function prepareFunctions() {
        $this->functions['curNS'] = 'curNS';
        $this->functions['getNS'] = 'getNS';
        $this->functions['noNS'] = 'noNS';
        $this->functions['p_get_first_heading'] = 'p_get_first_heading';
    }
}
