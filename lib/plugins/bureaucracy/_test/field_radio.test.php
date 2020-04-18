<?php

namespace dokuwiki\plugin\bureaucracy\test;

use Doku_Form;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_field_radio_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                'fruits:@@radioLabel@@',
                'radio "radioLabel" "Peaches|Apples|Oranges"',
                'Peaches',
                'fruits:Peaches',
                [],
                'first option chosen',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param string $formSyntax
     * @param        $postedValue
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_field_radio_submit(
        $templateSyntax,
        $formSyntax,
        $postedValue,
        $expectedWikiText,
        $expectedValidationErrors,
        $msg
    ) {
        $actualValidationErrors = [];

        $label = 'radio';
        $actualWikiText = parent::send_form_action_template(
            $formSyntax,
            $templateSyntax,
            $actualValidationErrors,
            $postedValue
        );

        $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
    }

    public function test_field_date_render()
    {
        $formSyntax = 'radio "radioLabel" "Peaches|Apples|Oranges"';
        $instr = p_get_instructions("<form>\n$formSyntax\n</form>");

        $actualHTML = p_render('xhtml', $instr, $info);

        $expectedFieldHTML = '<label class="radiolabel "><span>radioLabel <sup>*</sup></span></label><label><input type="radio" name="bureaucracy[0]" value="Peaches" /> <span>Peaches</span></label>
<label><input type="radio" name="bureaucracy[0]" value="Apples" /> <span>Apples</span></label>
<label><input type="radio" name="bureaucracy[0]" value="Oranges" /> <span>Oranges</span></label>';
        $expectedHTML = self::FORM_PREFIX_HTML . "\n$expectedFieldHTML\n" . self::FORM_SUFFIX_HTML;
        $this->assertEquals(trim($expectedHTML), trim($actualHTML));
    }
}
