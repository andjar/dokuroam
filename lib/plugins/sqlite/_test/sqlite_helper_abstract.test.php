<?php
/**
 * Test the abstract functions in the helper to check backend unity and some other functions
 *
 * @group plugin_sqlite
 * @group plugins
 */
class sqlite_helper_abstract_test extends DokuWikiTest {
    function setUp() {
        $this->pluginsEnabled[] = 'data';
        $this->pluginsEnabled[] = 'sqlite';
        parent::setUp();
    }

    /**
     * @return helper_plugin_sqlite
     * @throws Exception when databse is not initialized
     */
    function getSqliteHelper() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = plugin_load('helper', 'sqlite');
        if(!$SqliteHelper->init("testdb", DOKU_PLUGIN."sqlite/_test/db")) {
            throw new Exception('Initializing Sqlite Helper fails!');
        }
        return $SqliteHelper;
    }

    /**
     * @return helper_plugin_sqlite
     */
    function getResultSelectquery() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $sql               = "SELECT * FROM testdata WHERE keyword='music'";
        $res               = $SqliteHelper->query($sql);
        $SqliteHelper->res = $res;
        return $SqliteHelper;
    }

    /**
     * @return helper_plugin_sqlite
     */
    function getResultInsertquery() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $sql               = "INSERT INTO testdata VALUES(20,'glass','Purple')";
        $res               = $SqliteHelper->query($sql);
        $SqliteHelper->res = $res;
        return $SqliteHelper;
    }

    function test_SQLstring2array() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $sqlstring1 = "INSERT INTO data VALUES('text','text ;text')";
        $sqlarray1  = array("INSERT INTO data VALUES('text','text ;text')");

        $sqlstring2 = "INSERT INTO data VALUES('text','text ;text');INSERT INTO data VALUES('text','te''xt ;text');";
        $sqlarray2  = array("INSERT INTO data VALUES('text','text ;text')", "INSERT INTO data VALUES('text','te''xt ;text')");

        $this->assertEquals($sqlarray1, $SqliteHelper->SQLstring2array($sqlstring1));
        $this->assertEquals($sqlarray2, $SqliteHelper->SQLstring2array($sqlstring2));
    }

    function test_SQLstring2array_complex(){
        $SqliteHelper = $this->getSqliteHelper();

        $input = <<<EOF
-- This is test data for the SQLstring2array function

INSERT INTO foo SET bar = '
some multi''d line string
-- not a comment
';

SELECT * FROM bar;
SELECT * FROM bax;

SELECT * FROM bar; SELECT * FROM bax;
";
EOF;

        $statements = $SqliteHelper->SQLstring2array($input);

        $this->assertEquals(6, count($statements), 'number of detected statements');

        $this->assertContains('some multi\'\'d line string', $statements[0]);
        $this->assertContains('-- not a comment', $statements[0]);

        $this->assertEquals('SELECT * FROM bar', $statements[1]);
        $this->assertEquals('SELECT * FROM bax', $statements[2]);
        $this->assertEquals('SELECT * FROM bar', $statements[3]);
        $this->assertEquals('SELECT * FROM bax', $statements[4]);
    }

    function test_prepareSql() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $sql1 = "SELECT * FROM cheese WHERE NOT 'ho''les' OR 'mouse'";
        $sql2 = "SELECT * FROM cheese WHERE NOT ? OR ?";

        $args1 = array();
        $args2 = array($sql1);
        $args3 = array($sql1, "ho'les", "mouse");
        $args4 = array($sql2, "ho'les", "mouse");
        $args5 = array($sql2, "mouse");

        $this->assertEquals(false, $SqliteHelper->getAdapter()->prepareSql($args1));
        $this->assertEquals($sql1, $SqliteHelper->getAdapter()->prepareSql($args2));
        $this->assertEquals(false, $SqliteHelper->getAdapter()->prepareSql($args3));
        $this->assertEquals($sql1, $SqliteHelper->getAdapter()->prepareSql($args4));
        $this->assertEquals(false, $SqliteHelper->getAdapter()->prepareSql($args5));
    }

    function test_quote_and_join() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $string       = "Co'mpl''ex \"st'\"ring";
        $vals         = array($string, $string);
        $quotedstring = "'Co''mpl''''ex \"st''\"ring','Co''mpl''''ex \"st''\"ring'";
        $this->assertEquals($quotedstring, $SqliteHelper->quote_and_join($vals));
    }

    function test_quote_string() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $string       = "Co'mpl''ex \"st'\"ring";
        $quotedstring = "'Co''mpl''''ex \"st''\"ring'";
        $this->assertEquals($quotedstring, $SqliteHelper->quote_string($string));
    }

    function test_escape_string() {
        /** @var $SqliteHelper helper_plugin_sqlite */
        $SqliteHelper = $this->getSqliteHelper();

        $string       = "Co'mpl''ex \"st'\"ring";
        $quotedstring = "Co''mpl''''ex \"st''\"ring";
        $this->assertEquals($quotedstring, $SqliteHelper->escape_string($string));
    }

    function test_query_select() {
        $SqliteHelper = $this->getResultSelectquery();
        $this->assertNotEquals(false, $SqliteHelper->res);

        //close cursor
        $SqliteHelper->res_close($SqliteHelper->res);
    }

    function test_res2arr_assoc() {
        $SqliteHelper = $this->getResultSelectquery();

        $resultassoc = Array(
            0 => Array('tid' => 3, 'keyword' => 'music', 'value' => 'happy'),
            1 => Array('tid' => 4, 'keyword' => 'music', 'value' => 'Classic'),
            2 => Array('tid' => 5, 'keyword' => 'music', 'value' => 'Pop'),
            3 => Array('tid' => 8, 'keyword' => 'music', 'value' => 'Pink'),
            4 => Array('tid' => 10, 'keyword' => 'music', 'value' => 'Boring')
        );

        $this->assertEquals($resultassoc, $SqliteHelper->res2arr($SqliteHelper->res, $assoc = true));
        $this->assertEquals(array(), $SqliteHelper->res2arr(false));
    }

    function test_res2arr_num() {
        $SqliteHelper = $this->getResultSelectquery();

        $resultnum = Array(
            0 => Array(0 => 3, 1 => 'music', 2 => 'happy'),
            1 => Array(0 => 4, 1 => 'music', 2 => 'Classic'),
            2 => Array(0 => 5, 1 => 'music', 2 => 'Pop'),
            3 => Array(0 => 8, 1 => 'music', 2 => 'Pink'),
            4 => Array(0 => 10, 1 => 'music', 2 => 'Boring')
        );

        $this->assertEquals($resultnum, $SqliteHelper->res2arr($SqliteHelper->res, $assoc = false));
    }

    function test_res2row() {
        $SqliteHelper = $this->getResultSelectquery();

        $result0 = Array('tid' => 3, 'keyword' => 'music', 'value' => 'happy',);
        $result2 = Array('tid' => 5, 'keyword' => 'music', 'value' => 'Pop',);

        $this->assertEquals(false, $SqliteHelper->res2row(false));
        $this->assertEquals($result0, $SqliteHelper->res2row($SqliteHelper->res));
        $SqliteHelper->res2row($SqliteHelper->res); // skip one row
        $this->assertEquals($result2, $SqliteHelper->res2row($SqliteHelper->res));

        //close cursor
        $SqliteHelper->res_close($SqliteHelper->res);
    }

    function test_res2single() {
        $SqliteHelper = $this->getResultSelectquery();

        $result1 = 3;
        $result2 = 4;

        $this->assertEquals(false, $SqliteHelper->res2single(false));
        $this->assertEquals($result1, $SqliteHelper->res2single($SqliteHelper->res));
        $this->assertEquals($result2, $SqliteHelper->res2single($SqliteHelper->res)); //next row

        //close cursor
        $SqliteHelper->res_close($SqliteHelper->res);
    }

    function test_res_fetch_array() {
        $SqliteHelper = $this->getResultSelectquery();

        $result0 = Array(0 => 3, 1 => 'music', 2 => 'happy');
        $result1 = Array(0 => 4, 1 => 'music', 2 => 'Classic');

        $this->assertEquals(false, $SqliteHelper->res_fetch_array(false));
        $this->assertEquals($result0, $SqliteHelper->res_fetch_array($SqliteHelper->res));
        $this->assertEquals($result1, $SqliteHelper->res_fetch_array($SqliteHelper->res)); //next row

        //close cursor
        $SqliteHelper->res_close($SqliteHelper->res);
    }

    function test_fetch_assoc() {
        $SqliteHelper = $this->getResultSelectquery();

        $result0 = Array('tid' => 3, 'keyword' => 'music', 'value' => 'happy',);
        $result1 = Array('tid' => 4, 'keyword' => 'music', 'value' => 'Classic');

        $this->assertEquals(false, $SqliteHelper->res_fetch_assoc(false));
        $this->assertEquals($result0, $SqliteHelper->res_fetch_assoc($SqliteHelper->res));
        $this->assertEquals($result1, $SqliteHelper->res_fetch_assoc($SqliteHelper->res)); //next row

        //close cursor
        $SqliteHelper->res_close($SqliteHelper->res);
    }

    function test_res2count() {
        $SqliteHelper = $this->getResultSelectquery();

        $result = 5;

        $this->assertSame(0, $SqliteHelper->res2count(false));
        $this->assertEquals($result, $SqliteHelper->res2count($SqliteHelper->res));
    }

    function test_countChanges() {
        $SqliteHelper = $this->getResultInsertquery();

        $this->assertSame(0, $SqliteHelper->countChanges(false), 'Empty result');
        $this->assertEquals(1, $SqliteHelper->countChanges($SqliteHelper->res), 'Insert result');
    }

    function test_serialize() {
        $SqliteHelper = $this->getSqliteHelper();

        $res = $SqliteHelper->query('SELECT * FROM testdata');
        $this->assertNotFalse($res);
        $SqliteHelper->res_close($res);

        $obj = unserialize(serialize($SqliteHelper));

        $res = $obj->query('SELECT * FROM testdata');
        $this->assertNotFalse($res);
        $obj->res_close($res);
    }
}
