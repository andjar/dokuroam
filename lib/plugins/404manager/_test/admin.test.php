<?php

/**
 * Created by IntelliJ IDEA.
 * User: gerard
 * Date: 03-09-2016
 * Time: 15:49

 * Unit Tests over simple function
 *
 * @group plugin_404manager
 * @group plugins
 */

require_once(__DIR__ . '/../admin.php');

class admin_plugin_404manager_test extends DokuWikiTest
{

    /**
     * Test if an expression is a regular expression pattern
     */
    public function test_expressionIsRegular()
    {

        // Not an expression
        $inputExpression = "Hallo";
        $isRegularExpression = admin_plugin_404manager::isRegularExpression($inputExpression);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->assertEquals(0,$isRegularExpression,"The term (".$inputExpression.") is not a regular expression");

        // A basic expression
        $inputExpression = "/Hallo/";
        $isRegularExpression = admin_plugin_404manager::isRegularExpression($inputExpression);
        $this->assertEquals(true,$isRegularExpression,"The term (".$inputExpression.") is a regular expression");

        // A complicated expression
        $inputExpression = "/(/path1/path2/)(.*)/";
        $isRegularExpression = admin_plugin_404manager::isRegularExpression($inputExpression);
        $this->assertEquals(true,$isRegularExpression,"The term (" . $inputExpression . ") is a regular expression");

    }

}
