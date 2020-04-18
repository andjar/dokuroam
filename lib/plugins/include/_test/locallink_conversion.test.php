<?php

if (!defined('DOKU_INC')) die();

/**
 * Test the conversion of local links to internal links if the page hasn't been fully included
 *
 * @group plugin_include
 * @group plugins
 */
class plugin_include_locallink_conversion_test extends DokuWikiTest {
    /** @var helper_plugin_include $helper */
    private $helper;

    public function setUp() {
        $this->pluginsEnabled[] = 'include';
        parent::setUp();

        $this->helper = plugin_load('helper', 'include');

        saveWikiText('included', 'Example content with link [[#jump]]', 'Test setup');
        idx_addPage('test:included');

        saveWikiText('test:includefull', '{{page>..:included}}', 'Test setup');
        idx_addPage('test:includefull');

        saveWikiText('test:includefirst', '{{page>..:included&firstseconly}}', 'Test setup');
        idx_addPage('test:includefirst');
    }

    public function testLocalConverted() {
        $html = p_wiki_xhtml('test:includefirst');
        $this->assertContains('href="'.wl('included').'#jump"', $html);
        $this->assertNotContains('href="#jump"', $html);
    }

    public function testLocalExistsIfIncluded() {
        $html = p_wiki_xhtml('test:includefull');
        $this->assertContains('href="#jump"', $html);
    }
}
