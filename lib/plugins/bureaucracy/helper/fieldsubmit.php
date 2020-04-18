<?php
/**
 * Class helper_plugin_bureaucracy_fieldsubmit
 *
 * Creates a submit button
 */
class helper_plugin_bureaucracy_fieldsubmit extends helper_plugin_bureaucracy_field {
    protected $mandatory_args = 1;
    static $captcha_displayed = array();
    static $captcha_checked = array();

    /**
     * Arguments:
     *  - cmd
     *  - label (optional)
     *  - ^ (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function initialize($args) {
        parent::initialize($args);
        // make always optional to prevent being marked as required
        $this->opt['optional'] = true;
    }

    /**
     * Render the field as XHTML
     *
     * @params array     $params Additional HTML specific parameters
     * @params Doku_Form $form   The target Doku_Form object
     * @params int       $formid unique identifier of the form which contains this field
     */
    public function renderfield($params, Doku_Form $form, $formid) {
        if(!isset(helper_plugin_bureaucracy_fieldsubmit::$captcha_displayed[$formid])) {
            helper_plugin_bureaucracy_fieldsubmit::$captcha_displayed[$formid] = true;
            /** @var helper_plugin_captcha $helper */
            $helper = null;
            if(@is_dir(DOKU_PLUGIN.'captcha')) $helper = plugin_load('helper','captcha');
            if(!is_null($helper) && $helper->isEnabled()){
                $form->addElement($helper->getHTML());
            }
        }
        $attr = array();
        if(isset($this->opt['id'])) {
            $attr['id'] = $this->opt['id'];
        }
        $this->tpl = form_makeButton('submit','', '@@DISPLAY|' . $this->getLang('submit') . '@@', $attr);
        parent::renderfield($params, $form, $formid);
    }

    /**
     * Handle a post to the field
     *
     * Accepts and validates a posted captcha value.
     *
     * @param string $value The passed value
     * @param helper_plugin_bureaucracy_field[] $fields (reference) form fields (POST handled upto $this field)
     * @param int    $index  index number of field in form
     * @param int    $formid unique identifier of the form which contains this field
     * @return bool Whether the posted form has a valid captcha
     */
    public function handle_post($value, &$fields, $index, $formid) {
        if ($this->hidden) {
            return true;
        }
        if(!isset(helper_plugin_bureaucracy_fieldsubmit::$captcha_checked[$formid])) {
            helper_plugin_bureaucracy_fieldsubmit::$captcha_checked[$formid] = true;
            // check CAPTCHA
            /** @var helper_plugin_captcha $helper */
            $helper = null;
            if(@is_dir(DOKU_PLUGIN.'captcha')) $helper = plugin_load('helper','captcha');
            if(!is_null($helper) && $helper->isEnabled()){
                return $helper->check();
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
    public function getParam($name) {
        return ($name === 'value') ? null : parent::getParam($name);
    }

}
