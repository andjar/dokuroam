<?php

if (!defined('DOKU_INC')) die();

/**
 * Test the conversion of media references in link titles
 *
 * @group plugin_include
 * @group plugins
 */
class plugin_include_media_linktitle_conversion_test extends DokuWikiTest {
    /** @var helper_plugin_include $helper */
    private $helper;

    public function setUp() {
        $this->pluginsEnabled[] = 'include';
        parent::setUp();

        $this->helper = plugin_load('helper', 'include');

        saveWikiText('wiki:included', <<<EOF
  * [[test|{{dokuwiki.png}}]]
  * [[#test|{{dokuwiki.png?w=200}}]]
  * [[doku>test|{{dokuwiki.png?w=300}}]]
  * [[test|{{https://www.dokuwiki.org/lib/tpl/dokuwiki/images/logo.png}}]]
EOF
            , 'Test setup');
        idx_addPage('wiki:included');

        saveWikiText('test:include', '{{page>..:wiki:included}}', 'Test setup');
        idx_addPage('test:include');
    }

    public function testInternalLinkTitleConversion() {
        $html = p_wiki_xhtml('test:include');
        $this->assertContains('src="'.ml('wiki:dokuwiki.png').'"', $html);
    }

    public function testLocalLinkTitleConversion() {
        $html = p_wiki_xhtml('test:include');
        $this->assertContains('src="'.ml('wiki:dokuwiki.png', array('w' => '200')).'"', $html);
    }

    public function testInterWikiLinkTitleConversion() {
        $html = p_wiki_xhtml('test:include');
        $this->assertContains('src="'.ml('wiki:dokuwiki.png', array('w' => '300')).'"', $html);
    }

    public function testExternalMediaNotConverted() {
        $html = p_wiki_xhtml('test:include');
        $this->assertContains('src="'.ml('https://www.dokuwiki.org/lib/tpl/dokuwiki/images/logo.png').'"', $html);
    }
}
