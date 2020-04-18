<?php
/**
 * Class helper_plugin_bureaucracy_fieldusers
 *
 * Create multi-user input, with autocompletion
 */
class helper_plugin_bureaucracy_fieldusers extends helper_plugin_bureaucracy_fieldtextbox {

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
        $this->tpl['class'] .= ' userspicker';
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
            '(?:\((?P<delimiter>.*?)\))?' .//delimiter
            '(?:\.(?P<attribute>.*?))?' .  //match attribute after "."
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
        //copy the value by default
        if (is_array($matches[2]) && count($matches[2]) == 2) {
            return is_null($value) || $value === false ? $matches[0] : $value;
        }

        $attribute = isset($matches['attribute']) ? $matches['attribute'] : '';
        //check if matched string containts a pair of brackets
        $delimiter = preg_match('/\(.*\)/s', $matches[0]) ? $matches['delimiter'] : ', ';
        $users     = array_map('trim', explode(',', $value));

        switch($attribute) {
            case '':
                return implode($delimiter, $users);
            case 'name':
            case 'mail':
                return implode($delimiter, array_map(function ($user) use ($auth, $attribute) {
                    return $auth->getUserData($user)[$attribute];
                }, $users));
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
        $users = array_filter(preg_split('/\s*,\s*/', $this->getParam('value')));
        foreach ($users as $user) {
            if ($auth->getUserData($user) === false) {
                throw new Exception(sprintf($this->getLang('e_users'), hsc($this->getParam('display'))));
            }
        }
    }
}
