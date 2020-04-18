<?php
/**
 * Class helper_plugin_bureaucracy_fieldpassword
 *
 * Creates a single line password input field
 */
class helper_plugin_bureaucracy_fieldpassword extends helper_plugin_bureaucracy_field {
    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - ^ (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    function initialize($args) {
        parent::initialize($args);

        $attr = array();
        if(!isset($this->opt['optional'])) {
            $attr['required'] = 'required';
        }
        $this->tpl = form_makePasswordField('@@NAME@@', '@@DISPLAY@@', '@@ID@@', '@@CLASS@@', $attr);

        if(!isset($this->opt['optional'])){
            $this->tpl['class'] .= ' required';
        }
    }
}
