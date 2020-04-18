<?php

/**
 * Base class for form fields
 *
 * This class provides basic functionality for many form fields. It supports
 * labels, basic validation and template-based XHTML output.
 *
 * @author Adrian Lang <lang@cosmocode.de>
 **/

/**
 * Class helper_plugin_bureaucracy_field
 *
 * base class for all the form fields
 */
class helper_plugin_bureaucracy_field extends syntax_plugin_bureaucracy {

    protected $mandatory_args = 2;
    public $opt = array();
    /** @var string|array */
    protected $tpl;
    protected $checks = array();
    public $hidden = false;
    protected $error = false;
    protected $checktypes = array(
        '/' => 'match',
        '<' => 'max',
        '>' => 'min'
    );

    /**
     * Construct a helper_plugin_bureaucracy_field object
     *
     * This constructor initializes a helper_plugin_bureaucracy_field object
     * based on a given definition.
     *
     * The first two items represent:
     *   * the type of the field
     *   * and the label the field has been given.
     * Additional arguments are type-specific mandatory extra arguments and optional arguments.
     *
     * The optional arguments may add constraints to the field value, provide a
     * default value, mark the field as optional or define that the field is
     * part of a pagename (when using the template action).
     *
     * Since the field objects are cached, this constructor may not reference
     * request data.
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function initialize($args) {
        $this->init($args);
        $this->standardArgs($args);
    }

    /**
     * Return false to prevent DokuWiki reusing instances of the plugin
     *
     * @return bool
     */
    public function isSingleton() {
        return false;
    }

    /**
     * Checks number of arguments and store 'cmd', 'label' and 'display' values
     *
     * @param array $args array with the definition
     */
    protected function init(&$args) {
        if(count($args) < $this->mandatory_args){
            msg(sprintf($this->getLang('e_missingargs'), hsc($args[0]),
                        hsc($args[1])), -1);
            return;
        }

        // get standard arguments
        $this->opt = array();
        foreach (array('cmd', 'label') as $key) {
            if (count($args) === 0) break;
            $this->opt[$key] = array_shift($args);
        }
        $this->opt['display'] = $this->opt['label']; // allow to modify display value independently
    }

    /**
     * Check for additional arguments and store their values
     *
     * @param array $args array with remaining definition arguments
     */
    protected function standardArgs($args) {
        // parse additional arguments
        foreach($args as $arg){
            if ($arg[0] == '=') {
                $this->setVal(substr($arg,1));
            } elseif ($arg == '!') {
                $this->opt['optional'] = true;
            } elseif ($arg == '^') {
                //only one field has focus
                if (helper_plugin_bureaucracy_field::hasFocus()) {
                    $this->opt['id'] = 'focus__this';
                }
            } elseif($arg == '@') {
                $this->opt['pagename'] = true;
            } elseif($arg == '@@') {
                $this->opt['replyto'] = true;
            } elseif(preg_match('/x\d/', $arg)) {
                $this->opt['rows'] = substr($arg,1);
            } elseif($arg[0] == '.') {
                $this->opt['class'] = substr($arg, 1);
            } elseif(preg_match('/^0{2,}$/', $arg)) {
                $this->opt['leadingzeros'] = strlen($arg);
            } elseif($arg[0].$arg[1] == '**') {
                $this->opt['matchexplanation'] = substr($arg,2);
            } else {
                $t = $arg[0];
                $d = substr($arg,1);
                if (in_array($t, array('>', '<')) && !is_numeric($d)) {
                    break;
                }
                if ($t == '/') {
                    if (substr($d, -1) !== '/') {
                        break;
                    }
                    $d = substr($d, 0, -1);
                }
                if (!isset($this->checktypes[$t]) || !method_exists($this, 'validate_' . $this->checktypes[$t])) {
                    msg(sprintf($this->getLang('e_unknownconstraint'), hsc($t).' ('.hsc($arg).')'), -1);
                    return;
                }
                $this->checks[] = array('t' => $t, 'd' => $d);
            }
        }
    }

    /**
     * Add parsed element to Form which generates XHTML
     *
     * Outputs the represented field using the passed Doku_Form object.
     * Additional parameters (CSS class & HTML name) are passed in $params.
     * HTML output is created by passing the template $this->tpl to the simple
     * template engine _parse_tpl.
     *
     * @param array     $params Additional HTML specific parameters
     * @param Doku_Form $form   The target Doku_Form object
     * @param int       $formid unique identifier of the form which contains this field
     */
    public function renderfield($params, Doku_Form $form, $formid) {
        $this->_handlePreload();
        if(!$form->_infieldset){
            $form->startFieldset('');
        }
        if ($this->error) {
            $params['class'] = 'bureaucracy_error';
        }

        $params = array_merge($this->opt, $params);
        $form->addElement($this->_parse_tpl($this->tpl, $params));
    }

    /**
     * Only the first use get the focus, next calls not
     *
     * @return bool
     */
    protected static function hasFocus(){
        static $focus = true;
        if($focus) {
            $focus = false;
            return true;
        } else {
            return false;
        }
    }


    /**
     * Check for preload value in the request url
     */
    protected function _handlePreload() {
        $preload_name = '@' . strtr($this->getParam('label'),' .','__') . '@';
        if (isset($_GET[$preload_name])) {
            $this->setVal($_GET[$preload_name]);
        }
    }

    /**
     * Handle a post to the field
     *
     * Accepts and validates a posted value.
     *
     * (Overridden by fieldset, which has as argument an array with the form array by reference)
     *
     * @param string $value  The passed value or array or null if none given
     * @param helper_plugin_bureaucracy_field[] $fields (reference) form fields (POST handled upto $this field)
     * @param int    $index  index number of field in form
     * @param int    $formid unique identifier of the form which contains this field
     * @return bool Whether the passed value is valid
     */
    public function handle_post($value, &$fields, $index, $formid) {
        return $this->hidden || $this->setVal($value);
    }

    /**
     * Get the field type
     *
     * @return string
     **/
    public function getFieldType() {
        return $this->opt['cmd'];
    }

    /**
     * Get the replacement pattern used by action
     *
     * @return string
     */
    public function getReplacementPattern() {
        $label = $this->getParam('label');
        $value = $this->getParam('value');

        if (is_array($value)) {
            return '/(@@|##)' . preg_quote($label, '/') .
                '(?:\((?P<delimiter>.*?)\))?' .//delimiter
                '(?:\|(?P<default>.*?))' . (count($value) == 0 ? '' : '?') .
                '\1/si';
        }

        return '/(@@|##)' . preg_quote($label, '/') .
            '(?:\|(.*?))' . (is_null($value) ? '' : '?') .
            '\1/si';
    }

    /**
     * Used as an callback for preg_replace_callback
     *
     * @param $matches
     * @return string
     */
    public function replacementMultiValueCallback($matches) {
        $value = $this->opt['value'];

        //default value
        if (is_null($value) || $value === false) {
            if (isset($matches['default']) && $matches['default'] != '') {
                return $matches['default'];
            }
            return $matches[0];
        }

        //check if matched string containts a pair of brackets
        $delimiter = preg_match('/\(.*\)/s', $matches[0]) ? $matches['delimiter'] : ', ';

        return implode($delimiter, $value);
    }

    /**
     * Get the value used by action
     * If value is a callback preg_replace_callback is called instead preg_replace
     *
     * @return mixed|string
     */
    public function getReplacementValue() {
        $value = $this->getParam('value');

        if (is_array($value)) {
            return array($this, 'replacementMultiValueCallback');
        }

        return is_null($value) || $value === false ? '$2' : $value;
    }

    /**
     * Validate value and stores it
     *
     * @param mixed $value value entered into field
     * @return bool whether the passed value is valid
     */
    protected function setVal($value) {
        if ($value === '') {
            $value = null;
        }
        $this->opt['value'] = $value;
        try {
            $this->_validate();
            $this->error = false;
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
            $this->error = true;
        }
        return !$this->error;
    }

    /**
     * Whether the field is true (used for depending fieldsets)
     *
     * @return bool whether field is set
     */
    public function isSet_() {
        return !is_null($this->getParam('value'));
    }

    /**
     * Validate value of field and throws exceptions for bad values.
     *
     * @throws Exception when field didn't validate.
     */
    protected function _validate() {
        $value = $this->getParam('value');
        if (is_null($value)) {
            if(!isset($this->opt['optional'])) {
                throw new Exception(sprintf($this->getLang('e_required'),hsc($this->opt['label'])));
            }
            return;
        }

        foreach ($this->checks as $check) {
            $checktype = $this->checktypes[$check['t']];
            if (!call_user_func(array($this, 'validate_' . $checktype), $check['d'], $value)) {
                //replacement is custom explanation or just the regexp or the requested value
                if(isset($this->opt['matchexplanation'])) {
                    $replacement = hsc($this->opt['matchexplanation']);
                } elseif($checktype == 'match') {
                    $replacement = sprintf($this->getLang('checkagainst'), hsc($check['d']));
                } else {
                    $replacement = hsc($check['d']);
                }

                throw new Exception(sprintf($this->getLang('e_' . $checktype), hsc($this->opt['label']), $replacement));
            }
        }
    }

    /**
     * Get an arbitrary parameter
     *
     * @param string $name
     * @return mixed|null
     */
    public function getParam($name) {
        if (!isset($this->opt[$name]) || $name === 'value' && $this->hidden) {
            return null;
        }
        if ($name === 'pagename') {
            // If $this->opt['pagename'] is set, return the escaped value of the field.
            $value = $this->getParam('value');
            if (is_null($value)) {
                return null;
            }
            global $conf;
            if($conf['useslash']) $value = str_replace('/',' ',$value);
            return str_replace(':',' ',$value);
        }
        return $this->opt[$name];
    }

    /**
     * Parse a template with given parameters
     *
     * Replaces variables specified like @@VARNAME|default@@ using the passed
     * value map.
     *
     * @param string|array $tpl    The template as string or array
     * @param array        $params A hash mapping parameters to values
     *
     * @return string|array The parsed template
     */
    protected function _parse_tpl($tpl, $params) {
        // addElement supports a special array format as well. In this case
        // not all elements should be escaped.
        $is_simple = !is_array($tpl);
        if ($is_simple) $tpl = array($tpl);

        foreach ($tpl as &$val) {
            // Select box passes options as an array. We do not escape those.
            if (is_array($val)) continue;

            // find all variables and their defaults or param values
            preg_match_all('/@@([A-Z]+)(?:\|((?:[^@]|@$|@[^@])*))?@@/', $val, $pregs);
            for ($i = 0 ; $i < count($pregs[2]) ; ++$i) {
                if (isset($params[strtolower($pregs[1][$i])])) {
                    $pregs[2][$i] = $params[strtolower($pregs[1][$i])];
                }
            }
            // we now have placeholders in $pregs[0] and their values in $pregs[2]
            $replacements = array(); // check if empty to prevent php 5.3 warning
            if (!empty($pregs[0])) {
                $replacements = array_combine($pregs[0], $pregs[2]);
            }

            if($is_simple){
                // for simple string templates, we escape all replacements
                $replacements = array_map('hsc', $replacements);
            }else{
                // for the array ones, we escape the label and display only
                if(isset($replacements['@@LABEL@@']))   $replacements['@@LABEL@@']   = hsc($replacements['@@LABEL@@']);
                if(isset($replacements['@@DISPLAY@@'])) $replacements['@@DISPLAY@@'] = hsc($replacements['@@DISPLAY@@']);
            }

            // we attach a mandatory marker to the display
            if(isset($replacements['@@DISPLAY@@']) && !isset($params['optional'])){
                $replacements['@@DISPLAY@@'] .= ' <sup>*</sup>';
            }
            $val = str_replace(array_keys($replacements), array_values($replacements), $val);
        }
        return $is_simple ? $tpl[0] : $tpl;
    }

    /**
     * Executed after performing the action hooks
     */
    public function after_action() {
    }

    /**
     * Constraint function: value of field should match this regexp
     *
     * @param string $d regexp
     * @param mixed $value
     * @return int|bool
     */
    protected function validate_match($d, $value) {
        return @preg_match('/' . $d . '/i', $value);
    }

    /**
     * Constraint function: value of field should be bigger
     *
     * @param int|number $d lower bound
     * @param mixed $value of field
     * @return bool
     */
    protected function validate_min($d, $value) {
        return $value > $d;
    }

    /**
     * Constraint function: value of field should be smaller
     *
     * @param int|number $d upper bound
     * @param mixed $value of field
     * @return bool
     */
    protected function validate_max($d, $value) {
        return $value < $d;
    }

    /**
     * Available methods
     *
     * @return array
     */
    public function getMethods() {
        $result = array();
        $result[] = array(
            'name' => 'initialize',
            'desc' => 'Initiate object, first parameters are at least cmd and label',
            'params' => array(
                'params' => 'array'
            )
        );
        $result[] = array(
            'name' => 'renderfield',
            'desc' => 'Add parsed element to Form which generates XHTML',
            'params' => array(
                'params' => 'array',
                'form' => 'Doku_Form',
                'formid' => 'integer'
            )
        );
        $result[] = array(
            'name' => 'handle_post',
            'desc' => 'Handle a post to the field',
            'params' => array(
                'value' => 'array',
                'fields' => 'helper_plugin_bureaucracy_field[]',
                'index' => 'Doku_Form',
                'formid' => 'integer'
            ),
            'return' => array('isvalid' => 'bool')
        );
        $result[] = array(
            'name' => 'getFieldType',
            'desc' => 'Get the field type',
            'return' => array('fieldtype' => 'string')
        );
        $result[] = array(
            'name' => 'isSet_',
            'desc' => 'Whether the field is true (used for depending fieldsets)  ',
            'return' => array('isset' => 'bool')
        );
        $result[] = array(
            'name' => 'getParam',
            'desc' => 'Get an arbitrary parameter',
            'params' => array(
                'name' => 'string'
            ),
            'return' => array('Parameter value' => 'mixed|null')
        );
        $result[] = array(
            'name' => 'after_action',
            'desc' => 'Executed after performing the action hooks'
        );
        return $result;
    }

}
