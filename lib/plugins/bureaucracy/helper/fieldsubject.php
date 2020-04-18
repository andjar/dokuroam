<?php
/**
 * Class helper_plugin_bureaucracy_fieldsubject
 *
 * Defines own subject for mail action from this form
 */
class helper_plugin_bureaucracy_fieldsubject extends helper_plugin_bureaucracy_field {
    /**
     * Arguments:
     *  - cmd
     *  - subjecttext
     */

    /**
     * Render the field as XHTML
     *
     * @params array     $params Additional HTML specific parameters
     * @params Doku_Form $form   The target Doku_Form object
     * @params int       $formid unique identifier of the form which contains this field
     */
    public function renderfield($params, Doku_Form $form, $formid) {
        $this->_handlePreload();
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
}