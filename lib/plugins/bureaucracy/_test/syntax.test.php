<?php
/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class syntax_plugin_bureaucracy_test extends DokuWikiTest {

    protected $pluginsEnabled = array('bureaucracy');

    public function test_generalFormOutput() {
        $input = file_get_contents(dirname(__FILE__) . '/input.txt');
        $xhtml = p_render('xhtml', p_get_instructions($input), $info);

        $doc = phpQuery::newDocument($xhtml);

        $this->assertEquals(1, pq('form.bureaucracy__plugin', $doc)->length);
        $this->assertEquals(6, pq('form.bureaucracy__plugin fieldset', $doc)->length);

        // standard input types
        $this->checkField($doc, 'Employee Name', 'input[type=text][value=Your Name].edit', true);
        $this->checkField($doc, 'Your Age', 'input[type=text].edit', true);
        $this->checkField($doc, 'Your E-Mail Address', 'input[type=text].edit', true);
        $this->checkField($doc, 'Occupation (optional)', 'input[type=text].edit');
        $this->checkField($doc, 'Some password', 'input[type=password].edit', true);

        // select field
        $select = $this->checkField($doc, 'Please select an option', 'select');
        $this->assertEquals(3, pq('option', $select)->length);
        $this->assertEquals(1, pq('option:selected', $select)->length);
        $this->assertEquals('Peaches', pq('option:selected', $select)->val());

        // static text
        $this->assertEquals(1, pq('p:contains(Some static text)', $doc)->length);

        // checkbox
        $cb = $this->checkField($doc, 'Read the agreement?', 'input[type=checkbox][value=1]');
        $this->assertEquals('1', pq('input[type=hidden][value=0]', $cb->parent())->length);

        // text area
        $this->checkField($doc, 'Tell me about your self', 'textarea.edit', true);

        // file field
        $this->checkField($doc, 'File1', 'input[type=file].edit', true);

        // submit button
        $this->assertEquals(1, pq('button[type=submit]:contains(Submit Query)')->length);

    }

    public function test_HTMLinclusion() {
        $input = file_get_contents(dirname(__FILE__) . '/input.txt');
        $xhtml = p_render('xhtml', p_get_instructions($input), $info);

        $doc = phpQuery::newDocument($xhtml);

        // HTML Check - there should be no bold tag anywhere
        $this->assertEquals(0, pq('bold', $doc)->length);
    }

    private function checkField($doc, $name, $check, $required=false) {

        $field = pq('form.bureaucracy__plugin label span:contains(' . $name . ')', $doc);
        $this->assertEquals(1, $field->length, "find span of $name");

        if($required){
            $this->assertEquals(1, pq('sup', $field)->length, "is mandatory of $name");
        }

        $label = $field->parent();
        $this->assertTrue($label->is('label'), "find label of $name");

        $input = pq($check, $label);
        $this->assertEquals(1, $input->length, "find check of $name");

        return $input;
    }

    public function test_parseline() {
        $match = 'textbox label0 "Test with spaces"
textbox LabelWithoutSpaces
textbox Label Without Spaces
textbox "Label with spaces" "Text with a quote""in text"
textbox Label2 " "
textbox Label3 """"
textbox Label4 " """ " """   " """
textbox Label5 """ "
textbox Label6 "" " "
textbox Label7 " "" "
textbox Label7 " ""
 "" ss"
textbox Label8';

        $expected = array(
            array('textbox', 'label0', 'Test with spaces'),
            array('textbox', 'LabelWithoutSpaces'),
            array('textbox', 'Label', 'Without', 'Spaces'),
            array('textbox', 'Label with spaces', 'Text with a quote"in text'),
            array('textbox', 'Label2', ' '),
            array('textbox', 'Label3', '"'),
            array('textbox', 'Label4', ' "', ' "', ' "'),
            array('textbox', 'Label5', '" '),
            array('textbox', 'Label6', '', ' '),
            array('textbox', 'Label7', ' " '),
            array('textbox', 'Label7', ' "
 " ss'),
            array('textbox', 'Label8')
        );

        $lines = explode("\n", $match);
        $i = 0;
        while(count($lines) > 0) {
            $line = trim(array_shift($lines));

            $syntaxcomponent = new syntax_plugin_bureaucracy();
            $actual = $this->callNonaccessibleMethod($syntaxcomponent, '_parse_line', array($line, &$lines));

            $this->assertEquals($expected[$i], $actual);
            $i++;
        }

    }

    /**
     * Test not accessible methods..
     *
     * @param string|object $obj
     * @param string $name
     * @param array $args
     * @return mixed
     */
    protected function callNonaccessibleMethod($obj, $name, array $args) {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

}
