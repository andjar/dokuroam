<?php
require_once dirname(__FILE__).'/sqlite_helper_abstract.test.php';

/**
 * Tests all the same things as the abstract test but skips the PDO driver
 *
 * @group plugin_sqlite
 * @group plugins
 */
class sqlite_helper_sqlite2_test extends sqlite_helper_abstract_test {

    function setup() {
        if(!function_exists('sqlite_open')){
            $this->markTestSkipped('The sqlite2 extension is not available.');
        }else{
            $_ENV['SQLITE_SKIP_PDO'] = true;
        }
        parent::setup();
    }

    function tearDown() {
        $_ENV['SQLITE_SKIP_PDO'] = false;
        parent::tearDown();
    }

}
