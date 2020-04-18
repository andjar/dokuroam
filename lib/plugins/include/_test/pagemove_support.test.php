<?php

/**
 * Tests the move support for adapting the syntax of the include plugin
 *
 * @group plugin_include
 * @group plugins
 */
class plugin_include_pagemove_support_test extends DokuWikiTest {
    public function setup() {
        $this->pluginsEnabled[] = 'move';
        $this->pluginsEnabled[] = 'include';
        parent::setup();
    }

    public function test_relative_include() {
        /** @var $move helper_plugin_move_op */
        $move = plugin_load('helper', 'move_op');
        if (!$move) {
            $this->markTestSkipped('the move plugin is not installed');
            return;
        }
        saveWikiText('editx', '{{page>start#start}} %%{{page>start}}%% {{section>wiki:syntax#tables&nofooter}} {{page>:}} {{section>test:start#test}}', 'Testcase created');
        idx_addPage('editx');
        $this->assertTrue($move->movePage('editx', 'test:editx'));
        $this->assertEquals('{{page>:start#start}} %%{{page>start}}%% {{section>wiki:syntax#tables&nofooter}} {{page>:}} {{section>test:start#test}}',rawWiki('test:editx'));
    }

    public function test_rename() {
        /** @var $move helper_plugin_move_op */
        $move = plugin_load('helper', 'move_op');
        if (!$move) {
            $this->markTestSkipped('the move plugin is not installed');
            return;
        }
        saveWikiText('editx', 'Page to rename', 'Testcase create');
        saveWikiText('links', '{{section>links#foo}} {{page>editx}} {{page>:eDitX&nofooter}} {{section>editx#test}} {{page>editx&nofooter}}', 'Testcase created');
        idx_addPage('editx');
        idx_addPage('links');

        $this->assertTrue($move->movePage('editx', 'test:edit'));
        $this->assertEquals('{{section>links#foo}} {{page>test:edit}} {{page>test:edit&nofooter}} {{section>test:edit#test}} {{page>test:edit&nofooter}}', rawWiki('links'));
    }

    public function test_relative_include_adaption() {
        /** @var $move helper_plugin_move_op */
        $move = plugin_load('helper', 'move_op');
        if (!$move) {
            $this->markTestSkipped('the move plugin is not installed');
            return;
        }

        $text = '====== Main ======

This is a test page

[[.1:page_1|link]]

{{page>.1:page_1&nofooter&noeditbutton}}

{{page>.1:page_2&nofooter&noeditbutton}}';

        saveWikiText('old:namespace:main', $text, 'Created');
        saveWikiText('old:namespace:1:page_1', 'Page 1', 'Created');
        saveWikiText('old:namespace:1:page_2', 'Page 2', 'Created');
        idx_addPage('old:namespace:main');
        idx_addPage('old:namespace:1:page_1');
        idx_addPage('old:namespace:1:page_2');

        $this->assertTrue($move->movePage('old:namespace:main', 'new:namespace:main'));
        $this->assertTrue($move->movePage('old:namespace:1:page_1', 'new:namespace:1:page_1'));
        $this->assertTrue($move->movePage('old:namespace:1:page_2', 'new:namespace:1:page_2'));
        $this->assertEquals($text, rawWiki('new:namespace:main'));
    }
}
