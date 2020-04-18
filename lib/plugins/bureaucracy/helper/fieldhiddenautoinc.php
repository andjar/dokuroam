<?php
/**
 * Class helper_plugin_bureaucracy_fieldhiddenautoinc
 *
 * Creates an invisible field with a number that increases by 1 on each form submit
 */
class helper_plugin_bureaucracy_fieldhiddenautoinc extends helper_plugin_bureaucracy_fieldnumber {

    /**
     * Arguments:
     *  - cmd
     *  - label
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    function initialize($args) {
        $args[] = '++';
        parent::initialize($args);
    }

    /**
     * Render the field as XHTML
     *
     * Outputs the represented field using the passed Doku_Form object.
     *
     * @param array     $params Additional HTML specific parameters
     * @param Doku_Form $form   The target Doku_Form object
     * @param $formid
     */
    function renderfield($params, Doku_Form $form, $formid) {
        $this->_handlePreload();
        $form->addHidden($params['name'], $this->getParam('value') . '');
    }

}
