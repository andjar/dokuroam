<?php
/*
 * @group plugin_include
 * @group plugins
 */
class plugin_include_safeindex_test extends DokuWikiTest {
    public function setup() {
        $this->pluginsEnabled[] = 'include';
        parent::setup();
    }

    public function test_safeindex() {
        global $conf;
        global $AUTH_ACL;
        $conf['superuser'] = 'john';
        $conf['useacl']    = 1;

        $AUTH_ACL = array(
            '*           @ALL           0',
            '*           @user          8',
            'public      @ALL           1',
        );

        $_SERVER['REMOTE_USER'] = 'john';

        saveWikiText('parent', "{{page>child}}\n\n[[public_link]]\n\n{{page>public}}", 'Test parent created');
        saveWikiText('child', "[[foo:private]]", 'Test child created');
        saveWikiText('public', "[[foo:public]]", 'Public page created');

        idx_addPage('parent');
        idx_addPage('child');
        idx_addPage('public');

        $this->assertEquals(array('parent', 'public'), ft_backlinks('foo:public'));
        $this->assertEquals(array('child'), ft_backlinks('foo:private'));
        $this->assertEquals(array('parent'), ft_backlinks('public_link'));
    }
}

