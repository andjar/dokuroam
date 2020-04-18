<?php
/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class syntax_plugin_bureaucracy_action_template_test extends DokuWikiTest {

    protected $pluginsEnabled = array('bureaucracy');

    public function testPrepareLanguagePlaceholderNoTranslate() {
        $action = $this->getTemplateClass();
        $action->prepareLanguagePlaceholder();

        $this->assertEquals('en', $action->values['__lang__']);
        $this->assertEquals('/@LANG@/', $action->patterns['__lang__']);
        $this->assertEquals('', $action->values['__trans__']);
        $this->assertEquals('/@TRANS@/', $action->patterns['__trans__']);
    }

    public function testPrepareLanguagePlaceholderTranslateDefaultNS() {
        global $conf;
        global $ID;

        $conf['plugin']['translation']['translations'] = 'de';
        $ID = 'bla';

        plugin_enable('translation');
        if (null === plugin_load('helper', 'translation')) return;

        $action = $this->getTemplateClass();
        $action->prepareLanguagePlaceholder();

        $this->assertEquals('en', $action->values['__lang__']);
        $this->assertEquals('/@LANG@/', $action->patterns['__lang__']);
        $this->assertEquals('', $action->values['__trans__']);
        $this->assertEquals('/@TRANS@/', $action->patterns['__trans__']);
    }

    public function testPrepareLanguagePlaceholderTranslateLanguageNS() {
        global $conf;
        global $ID;

        $conf['plugin']['translation']['translations'] = 'de';
        $ID = 'de:bla';

        plugin_enable('translation');
        $translation = plugin_load('helper', 'translation');
        if (null === $translation) return;

        $action = $this->getTemplateClass();
        $action->prepareLanguagePlaceholder();

        $this->assertEquals('en', $action->values['__lang__']);
        $this->assertEquals('/@LANG@/', $action->patterns['__lang__']);
        $this->assertEquals('de', $action->values['__trans__']);
        $this->assertEquals('/@TRANS@/', $action->patterns['__trans__']);
    }

    public function testProcessFields() {
        $data = array();
        /** @var helper_plugin_bureaucracy_fieldstatic $staticfield */
        $staticfield = plugin_load('helper', 'bureaucracy_fieldstatic');
        $staticfield->initialize(array('text', 'text1'));
        $data[] = $staticfield;

        $action = $this->getTemplateClass();
        $action->prepareFieldReplacements($data, '_', '');

        $this->assertEquals('/(@@|##)text1(?:\|(.*?))\1/si', $action->patterns['text1']);
        $this->assertEquals('$2', $action->values['text1']);
        $this->assertEmpty($action->targetpages);
    }

    /**
     * @return helper_plugin_bureaucracy_actiontemplate
     */
    private function getTemplateClass() {
        /** @var helper_plugin_bureaucracy_actiontemplate $templateaction */
        $templateaction = plugin_load('helper', 'bureaucracy_actiontemplate');
        $templateaction->patterns = array();
        $templateaction->values = array();
        $templateaction->targetpages = array();
        $templateaction->pagename = array();
        return $templateaction;
    }


}
