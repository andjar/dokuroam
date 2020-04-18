<?php

/**
 * Tests the basic functionality of the tag and topic syntax
 */
class topic_tag_test extends DokuWikiTest {
    function setup() {
        $this->pluginsEnabled[] = 'tag';
        $this->pluginsEnabled[] = 'pagelist';
        parent::setup();
    }

    function test_topic_tag() {
        saveWikiText(
            'tagged_page',
            '{{tag>mytag test2tag}}', 'Test'
        );
        saveWikiText(
            'topic_page',
            '{{topic>mytag}}'.DOKU_LF.DOKU_LF.'{{tag>topictag mytag}}'.DOKU_LF, 'Test'
        );
        idx_addPage('topic_page');
        idx_addPage('tagged_page');
        $this->assertContains('tag:topictag', p_wiki_xhtml('topic_page'), 'Page with tag syntax doesn\'t contain tag output');
        $this->assertNotContains('tag:test2tag', p_wiki_xhtml('topic_page'), 'Page with tag and topic syntax tag which is listed in a page that is listed in the topic syntax but not on the page itself');
        $this->assertContains('topic_page', p_wiki_xhtml('topic_page'), 'Page with topic and tag syntax doesn\'t list itself in the topic syntax');
        $this->assertContains('tagged_page', p_wiki_xhtml('topic_page'), 'Page with topic syntax doesn\'t list matching page');
        $this->assertContains('tag:mytag', p_wiki_xhtml('tagged_page'), 'Page with tag syntax doesn\'t contain tag output');
        $this->assertContains('tag:test2tag', p_wiki_xhtml('tagged_page'), 'Page with tag syntax doesn\'t contain tag output');
        $this->assertNotContains('tag:topictag', p_wiki_xhtml('tagged_page'), 'Page with tag syntax contains tag from a page in which it is listed in the topic syntax');
        saveWikiText('tagged_page', '{{tag>test2tag}}', 'Deleted mytag');
        $this->assertNotContains('tagged_page', p_wiki_xhtml('topic_page'), 'Page that no longer contains the tag is still listed in the topic syntax (caching problems?)');
        $this->assertNotContains('tag:mytag', p_wiki_xhtml('tagged_page'), 'Removed tag is still listed in XHTML output');

    }
}
