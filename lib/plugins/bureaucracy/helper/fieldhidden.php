<?php
/**
 * Class helper_plugin_bureaucracy_fieldhidden
 *
 * Creates an invisible field with static data
 */
class helper_plugin_bureaucracy_fieldhidden extends helper_plugin_bureaucracy_field {

    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - =default value
     */

    /**
     * Render the field as XHTML
     *
     * Outputs the represented field using the passed Doku_Form object.
     *
     * @param array     $params Additional HTML specific parameters
     * @param Doku_Form $form   The target Doku_Form object
     * @param int       $formid unique identifier of the form which contains this field
     */
    function renderfield($params, Doku_Form $form, $formid) {
        $this->_handlePreload();
        //$form->addHidden($params['name'], $this->getParam('value'). '');
        $tlp = $this->getParam('value');
        $ins = array_slice(p_get_instructions($tlp), 2, -2);
        $tlp = p_render('xhtml', $ins, $byref_ignore);
        // $tlp = p_render('xhtml', , $byref_ignore);
        // $tlp = substring($tlp,2,-2);
        $form->addHidden($params['name'], $tlp. '');
    }

    /**
     * Get an arbitrary parameter
     *
     * @param string $name
     * @return mixed|null
     */
    function getParam($name) {
        if (!isset($this->opt[$name]) || in_array($name, array('pagename', 'value')) && $this->hidden) {
            return null;
        }
        if ($name === 'pagename') {
            // If $this->opt['pagename'] is set, return the value of the field,
            // UNESCAPED.
            $name = 'value';
            // $ins = p_get_instructions($this->opt[$name]);
            // $this->opt[$name] = "Hei"; //.= p_render('xhtml', $ins, $byref_ignore);
        }
        return $this->opt[$name];
    }
}
