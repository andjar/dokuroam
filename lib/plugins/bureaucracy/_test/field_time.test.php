<?php

namespace dokuwiki\plugin\bureaucracy\test;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_field_time_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                'time:@@timeLabel@@',
                'time timeLabel',
                '10:32',
                'time:10:32',
                [],
                'valid time without seconds',
            ],
            [
                'time:@@timeLabel@@',
                'time timeLabel',
                '10:32:44',
                'time:10:32:44',
                [],
                'valid time with seconds',
            ],
            [
                'time:@@timeLabel@@',
                'time timeLabel',
                '1032',
                null,
                ['timeLabel'],
                'invalid time',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string   $templateSyntax
     * @param string   $formSyntax
     * @param string   $postedValue
     * @param string   $expectedWikiText
     * @param string[] $expectedValidationErrors
     * @param string   $msg
     */
    public function test_field_time_submit(
        $templateSyntax,
        $formSyntax,
        $postedValue,
        $expectedWikiText,
        $expectedValidationErrors,
        $msg
    ) {
        $actualValidationErrors = [];

        $actualWikiText = parent::send_form_action_template(
            $formSyntax,
            $templateSyntax,
            $actualValidationErrors,
            $postedValue
        );

        if (empty($expectedValidationErrors)) {
            $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
        }
        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
    }

    public function test_field_time_render()
    {
        $formSyntax = 'time "timeLabel"';
        $instr = p_get_instructions("<form>\n$formSyntax\n</form>");

        $actualHTML = p_render('xhtml', $instr, $info);

        $expectedFieldHTML = '<label><span>timeLabel <sup>*</sup></span> <input type="text" name="bureaucracy[0]" class="timefield edit required" maxlength="8" required="required" /></label>';
        $expectedHTML = self::FORM_PREFIX_HTML . "\n$expectedFieldHTML\n" . self::FORM_SUFFIX_HTML;
        $this->assertEquals(trim($expectedHTML), trim($actualHTML));
    }
}
