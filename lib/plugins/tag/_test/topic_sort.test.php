<?php

if (!defined('DOKU_INC')) die();

/**
 * Tests the tagRefine function of the tag plugin
 */
class plugin_tag_topic_sorting_test extends DokuWikiTest {
    private $pages = array(
        'a',
        'aa',
        'a:a',
        'a:aa',
        'a:a:c',
        'a:a:b:a',
        'a:b:c'
    );
    /** @var helper_plugin_tag $helper */
    private $helper;

    public function setUp() {
        global $conf;
        $this->pluginsEnabled[] = 'tag';
        parent::setUp();

        $conf['plugin']['tag']['sortkey'] = 'ns';

        $this->helper = plugin_load('helper', 'tag');


        foreach ($this->pages as $page) {
            saveWikiText(
                $page,
                '{{tag>mytag}}', 'Test'
            );
            idx_addPage($page);
        }
    }

    public function test_ns_sort() {
        $this->assertEquals($this->pages, $this->extract_ids($this->helper->getTopic('', null, 'mytag')));
    }


    /**
     * Extract the id attribute of the supplied pages
     *
     * @param array $pages The pages that shall be used
     * @return array The ids of the pages
     */
    private function extract_ids($pages) {
        $result = array();
        foreach ($pages as $page) {
            $result[] = $page['id'];
        }
        return $result;
    }

}
