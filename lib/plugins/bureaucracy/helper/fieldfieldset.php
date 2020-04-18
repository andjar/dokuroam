<?php
/**
 * Class helper_plugin_bureaucracy_fieldfieldset
 *
 * Creates a new set of fields, which optional can be shown/hidden depending on the value of another field above it.
 */
class helper_plugin_bureaucracy_fieldfieldset extends helper_plugin_bureaucracy_field {
    protected $mandatory_args = 1;
    /** @var array with zero, one entry (fieldname) or two entries (fieldname and match value) */
    public $depends_on = array();

    /**
     * Arguments:
     *  - cmd
     *  - label (optional)
     *  - field name where switching depends on (optional)
     *  - match value (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function initialize($args) {
        // get standard arguments
        $this->opt = array('cmd' => array_shift($args));

        if (count($args) > 0) {
            $this->opt['label'] = array_shift($args);
            $this->opt['display'] = $this->opt['label'];

            $this->depends_on = $args;
        }
    }

    /**
     * Render the top of the fieldset as XHTML
     *
     * @param array     $params Additional HTML specific parameters
     * @param Doku_Form $form   The target Doku_Form object
     * @param int       $formid unique identifier of the form which contains this field
     */
    function renderfield($params, Doku_Form $form, $formid) {
        $form->startFieldset(hsc($this->getParam('display')));
        if (!empty($this->depends_on)) {
            $dependencies = array_map('hsc',(array) $this->depends_on);
            if (count($this->depends_on) > 1) {
                $msg = 'Only edit this fieldset if ' .
                       '“<span class="bureaucracy_depends_fname">%s</span>” '.
                       'is set to “<span class="bureaucracy_depends_fvalue">%s</span>”.';
            } else {
                $msg = 'Only edit this fieldset if ' .
                       '“<span class="bureaucracy_depends_fname">%s</span>” is set.';
            }

            $form->addElement('<p class="bureaucracy_depends">' . vsprintf($msg, $dependencies) . '</p>');
        }
    }

    /**
     * Handle a post to the fieldset
     *
     * When fieldset is closed, set containing fields to hidden
     *
     * @param null $value field value of fieldset always empty
     * @param helper_plugin_bureaucracy_field[] $fields (reference) form fields (POST handled upto $this field)
     * @param int    $index  index number of field in form
     * @param int    $formid unique identifier of the form which contains this field
     * @return bool Whether the passed value is valid
     */
    public function handle_post($value, &$fields, $index, $formid) {
        if(empty($this->depends_on)) {
            return true;
        }

        // search the field where fieldset depends on in fields before fieldset
        $hidden = false;
        for ($n = 0 ; $n < $index; ++$n) {
            $field = $fields[$n];
            if ($field->getParam('label') != $this->depends_on[0]) {
                continue;
            }
            if(count($this->depends_on) > 1) {
                $hidden = $field->getParam('value') != $this->depends_on[1];
            } else {
                $hidden = !$field->isSet_();
            }
            break;
        }
        // mark fields after this fieldset as hidden
        if ($hidden) {
            $this->hidden = true;
            for ($n = $index + 1 ; $n < count($fields) ; ++$n) {
                $field = $fields[$n];
                if ($field->getFieldType() === 'fieldset') {
                    break;
                }
                $field->hidden = true;
            }
        }
        return true;
    }

    /**
     * Get an arbitrary parameter
     *
     * @param string $name
     * @return mixed|null
     */
    function getParam($name) {
        if($name === 'value') {
            return null;
        } else {
            return parent::getParam($name);
        }
    }
}
