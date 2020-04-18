<?php

namespace dokuwiki\plugin\bureaucracy\test;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_field_yesno_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                'cb:@@checkbox@@',
                '"=Yes"',
                '"!No"',
                '1',
                'cb:Yes',
                [],
                'default checked substitution',
            ],
            [
                'cb:@@checkbox@@',
                '"=Yes"',
                '"!No"',
                '0',
                'cb:No',
                [],
                'default unchecked substitution',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param        $YesValue
     * @param        $NoValue
     * @param        $isChecked
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_field_yesno(
        $templateSyntax,
        $YesValue,
        $NoValue,
        $isChecked,
        $expectedWikiText,
        $expectedValidationErrors,
        $msg
    ) {
        $actualValidationErrors = [];

        $label = 'checkbox';
        $actualWikiText = parent::send_form_action_template(
            "yesno \"$label\" $YesValue $NoValue",
            $templateSyntax,
            $actualValidationErrors,
            $isChecked
        );

        $this->assertEquals($expectedWikiText, $actualWikiText, $msg);
        $this->assertEquals($expectedValidationErrors, $actualValidationErrors, $msg);
    }
}