<?php
/**
 * Base class for bureaucracy actions.
 *
 * All bureaucracy actions have to inherit from this class.
 *
 * ATM this class is pretty empty but, in the future it could be used to add
 * helper functions which can be utilized by the different actions.
 *
 * @author Michael Klier <chi@chimeric.de>
 */
class helper_plugin_bureaucracy_action extends syntax_plugin_bureaucracy {

    /**
     * Return false to prevent DokuWiki reusing instances of the plugin
     *
     * @return bool
     */
    public function isSingleton() {
        return false;
    }

    /**
     * Handle the user input [required]
     *
     * This function needs to be implemented to accept the user data collected
     * from the form. Data has to be grabbed from $_POST['bureaucracy'] using
     * the indicies in the 'idx' members of the $data items.
     *
     * @param helper_plugin_bureaucracy_field[] $fields the list of fields in the form
     * @param string                            $thanks the thank you message as defined in the form
     *                                                  or default one. Might be modified by the action
     *                                                  before returned
     * @param array                             $argv   additional arguments passed to the action
     * @return bool|string false on error, $thanks on success
     */
    public function run($fields, $thanks, $argv){
        msg('ERROR: called action %s did not implement a run() function');
        return false;
    }

    /**
     * Adds some language related replacement patterns
     */
    function prepareLanguagePlaceholder() {
        global $ID;
        global $conf;

        $this->patterns['__lang__'] = '/@LANG@/';
        $this->values['__lang__'] = $conf['lang'];

        $this->patterns['__trans__'] = '/@TRANS@/';
        $this->values['__trans__'] = '';

        /** @var helper_plugin_translation $trans */
        $trans = plugin_load('helper', 'translation');
        if (!$trans) return;

        $this->values['__trans__'] = $trans->getLangPart($ID);
        $this->values['__lang__'] = $trans->realLC('');
    }

    /**
     * Adds replacement pattern for fieldlabels (e.g @@Label@@)
     *
     * @param helper_plugin_bureaucracy_field $field
     */
    function prepareFieldReplacement($field) {
        $label = $field->getParam('label');

        if(!is_null($label)) {
            $this->patterns[$label] = $field->getReplacementPattern();
            $this->values[$label] = $field->getReplacementValue();
        }
    }

    /**
     * Adds <noinclude></noinclude> to replacement patterns
     */
    function prepareNoincludeReplacement() {
        $this->patterns['__noinclude__'] = '/<noinclude>(.*?)<\/noinclude>/is';
        $this->values['__noinclude__'] = '';
    }

    /**
     * Generate field replacements
     *
     * @param helper_plugin_bureaucracy_field[]  $fields  List of field objects
     * @return array
     */
    function prepareFieldReplacements($fields) {
        foreach ($fields as $field) {
            //field replacements
            $this->prepareFieldReplacement($field);
        }
    }

    /**
     * Returns ACL access level of the user or the (virtual) 'runas' user
     *
     * @param string $id pageid
     * @return int
     */
    protected function aclcheck($id) {
        $runas = $this->getConf('runas');

        if($runas) {
            $auth = auth_aclcheck($id, $runas, array());
        } else {
            $auth = auth_quickaclcheck($id);
        }
        return $auth;

    }

    /**
     * Available methods
     *
     * @return array
     */
    public function getMethods() {
        $result = array();
        $result[] = array(
            'name' => 'run',
            'desc' => 'Handle the user input',
            'params' => array(
                'fields' => 'helper_plugin_bureaucracy_field[]',
                'thanks' => 'string',
                'argv'   => 'array'
            ),
            'return' => array('false on error, thanks message on success' => 'bool|string')
        );
        return $result;
    }

}
