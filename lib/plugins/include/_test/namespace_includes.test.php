<?php

if (!defined('DOKU_INC')) die();

/**
 * Test namespace includes
 *
 * @group plugin_include
 * @group plugins
 */
class plugin_include_namespaces_includes_test extends DokuWikiTest {
    /**
     * @var helper_plugin_include $helper
     */
    private $helper;

    /**
     * Setup - enable and load the include plugin and create the test pages
     */
    public function setup() {
        $this->pluginsEnabled[] = 'include';
        parent::setup(); // this enables the include plugin
        $this->helper = plugin_load('helper', 'include');

        global $conf;
        $conf['hidepages'] = 'inclhidden:hidden';

        // for testing hidden pages
        saveWikiText('inclhidden:hidden', 'Hidden page', 'Created hidden page');
        saveWikiText('inclhidden:visible', 'Visible page', 'Created visible page');

        // pages on different levels
        saveWikiText('incltest:level1', 'Page on level 1', 'Created page on level 1');
        saveWikiText('incltest:ns:level2', 'Page on level 2', 'Created page on level 2');
        saveWikiText('incltest:ns:ns:level3', 'Page on level 3', 'Created page on level 3');

        // for page ordering
        saveWikiText('inclorder:page1', 'Page 1', 'Created page 1');
        saveWikiText('inclorder:page2', 'Page 2', 'Created page 2');
        saveWikiText('inclorder:page3', '{{include_n>10}} Page 3/10', 'created page 3/1');
        saveWikiText('inclorder:page4', '{{include_n>2}} Page 4/2', 'created page 4/0');
    }

    /**
     * Helper function to read dir content
     */
    protected function getDirContent ($dir) {
        if (is_dir($dir)) {
            $pages = array();
            if ($handle = opendir($dir)) {
                while (($file = readdir($handle)) !== false) {
                    if ($file != '.' && $file != '..') {
                        $pages [] = $file;
                    }
                }
                closedir($handle);
                return $pages;
            }
        }
        return null;
    }

    /**
     * Test hiding of hidden pages in namespace includes
     */
    public function test_hidden() {
        $flags = $this->helper->get_flags(array());
        $pages = $this->helper->_get_included_pages('namespace', 'inclhidden:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'inclhidden:visible', 'exists' => true, 'parent_id' => ''),
                            ), $pages);
    }

    /**
     * Test include depth limit
     */
    public function test_depth() {
        $flags = $this->helper->get_flags(array());
        $pages = $this->helper->_get_included_pages('namespace', 'incltest:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'incltest:level1', 'exists' => true, 'parent_id' => ''),
                            ), $pages);
        $flags = $this->helper->get_flags(array('depth=2'));
        $pages = $this->helper->_get_included_pages('namespace', 'incltest:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'incltest:level1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'incltest:ns:level2', 'exists' => true, 'parent_id' => ''),
                            ), $pages);
        $flags = $this->helper->get_flags(array('depth=2'));
        $pages = $this->helper->_get_included_pages('namespace', 'incltest:ns', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'incltest:ns:level2', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'incltest:ns:ns:level3', 'exists' => true, 'parent_id' => ''),
                            ), $pages);
        $flags = $this->helper->get_flags(array('depth=0'));
        $pages = $this->helper->_get_included_pages('namespace', 'incltest:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'incltest:level1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'incltest:ns:level2', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'incltest:ns:ns:level3', 'exists' => true, 'parent_id' => ''),
                            ), $pages);

        // test include of the root namespace
        $flags = $this->helper->get_flags(array());
        $pages = $this->helper->_get_included_pages('namespace', ':', '', '', $flags);
        $this->assertEquals(array(array('id' => 'mailinglist', 'exists' => true, 'parent_id' => '')), $pages);
        $flags = $this->helper->get_flags(array('depth=2'));
        $pages = $this->helper->_get_included_pages('namespace', ':', '', '', $flags);
        $expected = array(
                                 array('id' => 'inclhidden:visible', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page2', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page3', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page4', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'incltest:level1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'mailinglist', 'exists' => true, 'parent_id' => ''),
                                 //array('id' => 'wiki:dokuwiki', 'exists' => true, 'parent_id' => ''),
                                 //array('id' => 'wiki:syntax', 'exists' => true, 'parent_id' => ''),
                                 //$wikiPages,
                            );

        // page int:editandsavetest exists in DokuWiki after September 2017
        if (page_exists('int:editandsavetest')) {
            $expected [] = array('id' => 'int:editandsavetest', 'exists' => true, 'parent_id' => '');
        }

        // Add pages in namespace wiki
        $dir = $this->getDirContent(dirname(__FILE__).'/../../../../_test/data/pages/wiki');
        $this->assertTrue($dir !== null);
        foreach ($dir as $page) {
            $page = substr($page, 0, -4);
            $expected [] = array('id' => 'wiki:'.$page, 'exists' => true, 'parent_id' => '');
        }

        array_multisort($expected);
        array_multisort($pages);
        $this->assertEquals($expected, $pages);
    }

    /**
     * Test ordering of namespace includes
     */
    public function test_order() {

        $flags = $this->helper->get_flags(array());
        $pages = $this->helper->_get_included_pages('namespace', 'inclorder:', '', '', $flags);

        $this->assertEquals(array(
                                 array('id' => 'inclorder:page1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page2', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page3', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page4', 'exists' => true, 'parent_id' => ''),
                            ), $pages);

        $flags = $this->helper->get_flags(array('rsort'));
        $pages = $this->helper->_get_included_pages('namespace', 'inclorder:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'inclorder:page4', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page3', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page2', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page1', 'exists' => true, 'parent_id' => ''),
                            ), $pages);
        $flags = $this->helper->get_flags(array('order=custom'));
        $pages = $this->helper->_get_included_pages('namespace', 'inclorder:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'inclorder:page4', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page3', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page2', 'exists' => true, 'parent_id' => ''),
                            ), $pages);

        $flags = $this->helper->get_flags(array('order=custom', 'rsort'));
        $pages = $this->helper->_get_included_pages('namespace', 'inclorder:', '', '', $flags);
        $this->assertEquals(array(
                                 array('id' => 'inclorder:page2', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page1', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page3', 'exists' => true, 'parent_id' => ''),
                                 array('id' => 'inclorder:page4', 'exists' => true, 'parent_id' => ''),
                            ), $pages);
    }
}
