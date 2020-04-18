<?php
/**
 * Class helper_plugin_bureaucracy_fieldtextbox
 *
 * Creates a single line input field
 */
class helper_plugin_bureaucracy_fieldtextbox extends helper_plugin_bureaucracy_field {

    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - =default (optional)
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

        $this->tpl = form_makeTextField('@@NAME@@', '@@VALUE@@', '@@DISPLAY@@', '@@ID@@', '@@CLASS@@', $attr);
        if(isset($this->opt['class'])){
            $this->tpl['class'] .= ' '.$this->opt['class'];
        }
        if(!isset($this->opt['optional'])){
            $this->tpl['class'] .= ' required';
        }
    }
}
