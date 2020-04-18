<?php
/**
 * Class helper_plugin_bureaucracy_fieldyesno
 *
 * Creates a checkbox
 */
class helper_plugin_bureaucracy_fieldyesno extends helper_plugin_bureaucracy_field {

    /**
     * Arguments:
     *  - cmd
     *  - label
     *  - =yesvalue
     *  - !falsevalue
     *  - ^ (optional)
     *
     * @param array $args The tokenized definition, only split at spaces
     */
    public function initialize($args) {
        $this->init($args);
        $newargs = array();
        foreach ($args as $arg) {
            switch ($arg[0]) {
            case '=':
                if($arg == '==1') {
                    $this->opt['value'] = '1';
                }elseif($arg == '==0') {
                    $this->opt['value'] = '0';
                }else{
                    $this->opt['true_value'] = substr($arg, 1);
                }
                break;
            case '!':
                $this->opt['false_value'] = substr($arg, 1);
                break;
            default:
                $newargs[] = $arg;
            }
        }
        $this->standardArgs($newargs);
        $this->opt['optional'] = true;
    }

    /**
     * Get an arbitrary parameter
     *
     * @param string $key
     * @return mixed|null
     */
    public function getParam($key) {
        if ($key === 'value') {
            if ($this->opt['value'] === '1') {
                return isset($this->opt['true_value']) ?
                       $this->opt['true_value'] :
                       null;
            } elseif ($this->opt['value'] === '0') {
                return isset($this->opt['false_value']) ?
                       $this->opt['false_value'] :
                       null;
            }
        }
        return parent::getParam($key);
    }

    /**
     * Whether the field is true (used for depending fieldsets)
     *
     * @return bool whether field is set
     */
    public function isSet_() {
        return $this->opt['value'] === '1';
    }

    /**
     * Render the field as XHTML
     *
     * @params array     $params Additional HTML specific parameters
     * @params Doku_Form $form   The target Doku_Form object
     * @params int       $formid unique identifier of the form which contains this field
     */
    public function renderfield($params, Doku_Form $form, $formid) {
        $id = 'bureaucracy__'.md5(rand());
        if(isset($this->opt['id'])) {
            $id = $this->opt['id'];
        }
        $params = array_merge(
            array('value' => false),
            $this->opt,
            $params
        );
        $check = $params['value'] ? 'checked="checked"' : '';
        $this->tpl = '<label class="@@CLASS@@" for="'.$id.'"><span>@@DISPLAY@@</span>'.
                     '<input type="hidden" name="@@NAME@@" value="0" />' .
                     '<input type="checkbox" name="@@NAME@@" value="1" id="'.$id.'" ' . $check . ' />' .
                     '</label>';
        parent::renderfield($params, $form, $formid);
    }
}
