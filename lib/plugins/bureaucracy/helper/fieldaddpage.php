<?php
/**
 * Class helper_plugin_bureaucracy_fieldaddpage
 *
 * Adds another page page_tgt based on a template page page_tpl only for use with the template action
 */
class helper_plugin_bureaucracy_fieldaddpage extends helper_plugin_bureaucracy_field {

    /**
     * Arguments:
     *  - cmd
     *  - page_tpl
     *  - page_tgt
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    function initialize($args) {
        if(count($args) < 3){
            msg(sprintf($this->getLang('e_missingargs'), hsc($args[0]),
                        hsc($args[1])), -1);
            return;
        }

        // get standard arguments
        $this->opt = array_combine(array('cmd', 'page_tpl', 'page_tgt'), $args);
    }

    /**
     * Nothing displayed
     *
     * @params array     $params Additional HTML specific parameters
     * @params Doku_Form $form   The target Doku_Form object
     * @params int       $formid unique identifier of the form which contains this field
     */
    function renderfield($params, Doku_Form $form, $formid) {
    }

    /**
     * Handle a post to the field
     *
     * @param string $value null
     * @param helper_plugin_bureaucracy_field[] $fields (reference) form fields (POST handled upto $this field)
     * @param int    $index  index number of field in form
     * @param int    $formid unique identifier of the form which contains this field
     * @return bool Whether the passed value is valid
     */
    function handle_post($value, &$fields, $index, $formid) {
        return true;
    }

    /**
     * Get an arbitrary parameter
     *
     * @param string $name
     * @return mixed|null
     */
    function getParam($name) {
        return ($name === 'value' ||
                (in_array($name, array('page_tpl', 'page_tgt')) && $this->hidden)) ?
               null :
               parent::getParam($name);
    }
}
