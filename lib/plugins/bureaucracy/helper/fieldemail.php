<?php
/**
 * Class helper_plugin_bureaucracy_fieldemail
 *
 * Creates a single line input field where the input is validated to be a valid email address
 */
class helper_plugin_bureaucracy_fieldemail extends helper_plugin_bureaucracy_fieldtextbox {

    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - @@ (optional)
     *  - ^ (optional)
     */

    /**
     * Validate field value
     *
     * @throws Exception when empty or not valid email address
     */
    function _validate() {
        parent::_validate();

        $value = $this->getParam('value');
        if(!is_null($value) && $value !== '@MAIL@' && !mail_isvalid($value)){
            throw new Exception(sprintf($this->getLang('e_email'),hsc($this->getParam('display'))));
        }
    }
}
