<?php
/**
 * Class helper_plugin_bureaucracy_fieldtime
 *
 * A time in the format (h)h:mm(:ss)
 */
class helper_plugin_bureaucracy_fieldtime extends helper_plugin_bureaucracy_fieldtextbox {
    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - ^ (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function initialize($args) {
        parent::initialize($args);
        $attr = array(
            'class' => 'timefield edit',
            'maxlength'=>'8'
        );
        if(!isset($this->opt['optional'])) {
            $attr['required'] = 'required';
            $attr['class'] .= ' required';
        }
        $this->tpl = form_makeTextField('@@NAME@@', '@@VALUE@@', '@@DISPLAY@@', '@@ID@@', '@@CLASS@@', $attr);
    }

    /**
     * Validate field input
     *
     * @throws Exception when empty or wrong time format
     */
    protected function _validate() {
        parent::_validate();

        $value = $this->getParam('value');
        if (!is_null($value) && !preg_match('/^\d{1,2}:\d{2}(?::\d{2})?$/', $value)) {
            throw new Exception(sprintf($this->getLang('e_time'),hsc($this->getParam('display'))));
        }
    }
}
