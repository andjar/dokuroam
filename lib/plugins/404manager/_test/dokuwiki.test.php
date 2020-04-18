<?php
/**
 * Tests over DokuWiki function for the 404manager plugin
 *
 * @group plugin_404manager
 * @group plugins
 */
require_once(__DIR__ . '/constant_parameters.php');

class dokuwiki_plugin_404manager_test extends DokuWikiTest
{

    public static function setUpBeforeClass()
    {

        parent::setUpBeforeClass();
        saveWikiText(constant_parameters::$PAGE_EXIST_ID, 'A page', 'Test initialization');
        idx_addPage(constant_parameters::$PAGE_EXIST_ID);

    }


    /**
     * Simple test to make sure the plugin.info.txt is in correct format
     */
    public function test_plugininfo()
    {

        $file = __DIR__ . '/../plugin.info.txt';
        $this->assertFileExists($file);

        $info = confToHash($file);

        $this->assertArrayHasKey('base', $info);
        $this->assertArrayHasKey('author', $info);
        $this->assertArrayHasKey('email', $info);
        $this->assertArrayHasKey('date', $info);
        $this->assertArrayHasKey('name', $info);
        $this->assertArrayHasKey('desc', $info);
        $this->assertArrayHasKey('url', $info);

        $this->assertEquals('404manager', $info['base']);
        $this->assertRegExp('/^https?:\/\//', $info['url']);
        $this->assertTrue(mail_isvalid($info['email']));
        $this->assertRegExp('/^\d\d\d\d-\d\d-\d\d$/', $info['date']);
        $this->assertTrue(false !== strtotime($info['date']));

    }

    /** Page exist can be tested on two ways within DokuWiki
     *   * page_exist
     *   * and the $INFO global variable
     */
    public function test_pageExists()
    {

        // Not in a request
        $this->assertTrue(page_exists(constant_parameters::$PAGE_EXIST_ID));

        // In a request
        $request = new TestRequest();
        $request->get(array('id' => constant_parameters::$PAGE_EXIST_ID), '/doku.php');
        $request->execute();
        global $INFO;
        $this->assertTrue($INFO['exists']);

        // Not in a request
        $this->assertFalse(page_exists(constant_parameters::$PAGE_DOES_NOT_EXIST_ID));

        // In a request
        $request = new TestRequest();
        $request->get(array('id' => constant_parameters::$PAGE_DOES_NOT_EXIST_ID), '/doku.php');
        $request->execute();
        global $INFO;
        $this->assertFalse($INFO['exists']);

    }

}
