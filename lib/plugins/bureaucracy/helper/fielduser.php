<?php
/**
 * Class helper_plugin_bureaucracy_fielduser
 *
 * Create single user input, with autocompletion
 */
class helper_plugin_bureaucracy_fielduser extends helper_plugin_bureaucracy_fieldtextbox {

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
        $this->tpl['class'] .= ' userpicker';
    }

    /**
     * Allow receiving user attributes by ".". Ex. user.name
     * You can pass an optional argument to user.grps enclosed in brackets, used as an groups delimiter Ex. user.grps(, )
     *
     * @return string
     */
    public function getReplacementPattern() {
        $label = $this->opt['label'];

        return '/(@@|##)' . preg_quote($label, '/') .
            '(?:\.(.*?))?' .    //match attribute after "."
            '(?:\((.*?)\))?' .  //match parameter enclosed in "()". Used for grps separator
            '\1/si';
    }

    /**
     * Used as an callback for preg_replace_callback
     *
     * @param $matches
     * @return string
     */
    public function replacementValueCallback($matches) {
        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;

        $value = $this->opt['value'];
        //attr doesn't exists
        if (!isset($matches[2])) {
            return is_null($value) || $value === false ? '' : $value;
        }
        $attr = $matches[2];

        $udata = $auth->getUserData($value);
        //no such user
        if ($udata === false) {
            return $matches[0];
        }

        switch($attr) {
            case 'name':
            case 'mail':
                return $udata[$attr];
            case 'grps':
                $delitmiter = ', ';
                if (isset($matches[3])) {
                    $delitmiter = $matches[3];
                }
                return implode($delitmiter, $udata['grps']);
            default:
                return $matches[0];
        }
    }

    /**
     * Return the callback for user replacement
     *
     * @return array
     */
    public function getReplacementValue() {
        return array($this, 'replacementValueCallback');
    }

    /**
     * Validate value of field
     *
     * @throws Exception when user not exists
     */
    protected function _validate() {
        parent::_validate();

        /** @var DokuWiki_Auth_Plugin $auth */
        global $auth;
        $value = $this->getParam('value');
        if (!is_null($value) && $auth->getUserData($value) === false) {
            throw new Exception(sprintf($this->getLang('e_user'),hsc($this->getParam('display'))));
        }
    }
}
