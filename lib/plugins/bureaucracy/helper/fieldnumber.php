<?php
/**
 * Class helper_plugin_bureaucracy_fieldnumber
 *
 * Creates a single line input field, where input is validated to be numeric
 */
class helper_plugin_bureaucracy_fieldnumber extends helper_plugin_bureaucracy_fieldtextbox {

    private $autoinc = false;

    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - ++ (optional)
     *  - 0000 (optional)
     *  - ^ (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function initialize($args) {
        $pp = array_search('++', $args, true);
        if ($pp !== false) {
            unset($args[$pp]);
            $this->autoinc = true;
        }

        parent::initialize($args);

        if ($this->autoinc) {
            global $ID;
            $key = $this->get_key();
            $c_val = p_get_metadata($ID, 'bureaucracy ' . $key);
            if (is_null($c_val)) {
                if (!isset($this->opt['value'])) {
                    $this->opt['value'] = 0;
                }
                p_set_metadata($ID, array('bureaucracy' => array($key => $this->opt['value'])));
            } else {
                $this->opt['value'] = $c_val;
            }
        }
        $this->opt['value'] = $this->addLeadingzeros($this->opt['value']);
    }

    /**
     * Validate field value
     *
     * @throws Exception when not a number
     */
    protected function _validate() {
        $value = $this->getParam('value');
        if (!is_null($value) && !is_numeric($value)){
            throw new Exception(sprintf($this->getLang('e_numeric'),hsc($this->getParam('display'))));
        }

        parent::_validate();
    }

    /**
     * Handle a post to the field
     *
     * Accepts and validates a posted value.
     *
     * @param string $value The passed value or array or null if none given
     * @param array  $fields (reference) form fields (POST handled upto $this field)
     * @param int    $index  index number of field in form
     * @param int    $formid unique identifier of the form which contains this field
     * @return bool Whether the passed value is valid
     */
    public function handle_post($value, &$fields, $index, $formid) {
        $value = $this->addLeadingzeros($value);

        return parent::handle_post($value, $fields, $index, $formid);
    }

    /**
     * Returns the cleaned key for this field required for metadata
     *
     * @return string key
     */
    private function get_key() {
        return preg_replace('/\W/', '', $this->opt['label']) . '_autoinc';
    }

    /**
     * Executed after performing the action hooks
     *
     * Increases counter and purge cache
     */
    public function after_action() {
        if ($this->autoinc) {
            global $ID;
            p_set_metadata($ID, array('bureaucracy' => array($this->get_key() => $this->opt['value'] + 1)));
            // Force rerendering by removing the instructions cache file
            $cache_fn = getCacheName(wikiFN($ID).$_SERVER['HTTP_HOST'].$_SERVER['SERVER_PORT'],'.'.'i');
            if (file_exists($cache_fn)) {
                unlink($cache_fn);
            }
        }
    }

    /**
     * Add leading zeros, depending on the corresponding field option
     *
     * @param int|string $value number
     * @return string
     */
    protected function addLeadingzeros(&$value) {
        if($this->opt['leadingzeros']) {
            $length = strlen($value);
            for($i = $length; $i < $this->opt['leadingzeros']; $i++) {
                $value = '0' . $value;
            }
            return $value;
        }
        return $value;
    }
}
