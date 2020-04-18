<?php

namespace dokuwiki\plugin\bureaucracy\test;

use \Doku_Form;

/**
 * @group plugin_bureaucracy
 * @group plugins
 */
class bureaucracy_field_hidden_test extends BureaucracyTest
{

    public function dataProvider()
    {
        return [
            [
                'hidden:@@hiddenLabel@@',
                'default value of the hidden field',
                'default value of the hidden field',
                'hidden:default value of the hidden field',
                [],
                'valid hidden',
            ],
        ];
    }

    /**
     * @dataProvider dataProvider
     *
     * @param string $templateSyntax
     * @param        $postedValue
     * @param string $expectedWikiText
     * @param string $expectedValidationErrors
     * @param string $msg
     *
     */
    public function test_field_hidden_submit(
        $templateSyntax,
        $defaultValue,
        $postedValue,
        $expectedWikiText,
        $expectedValidationErrors,
        $msg
    ) {
        $actualValidationErrors = [];

        $label = 'hiddenLabel';
        $actualWikiText = parent::send_form_action_template(
            "hidden \"$label\" \"=$defaultValue\"",
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
        $formSyntax = 'hidden hiddenLabel "=default value of the hidden field"';
        $instr = p_get_instructions("<form>\n$formSyntax\n</form>");

        $actualHTML = p_render('xhtml', $instr, $info);

        $hiddenFormPrefix = '<form class="bureaucracy__plugin" id="bureaucracy__plugin1" enctype="multipart/form-data" method="post" action="" accept-charset="utf-8"><div class="no">
<input type="hidden" name="sectok" value="" /><input type="hidden" name="bureaucracy[$$id]" value="1" />';
        $expectedFieldHTML = '<input type="hidden" name="bureaucracy[0]" value="default value of the hidden field" />';
        $hiddenFormSuffix = '</div></form>';
        $expectedHTML = "$hiddenFormPrefix$expectedFieldHTML$hiddenFormSuffix";

        $this->assertEquals(trim($expectedHTML), trim($actualHTML));
    }
}
